<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SocialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_delete_another_users_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'A post',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('posts.destroy', $post));

        $response->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_post_owner_can_delete_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_post_content_cannot_exceed_500_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'content' => str_repeat('a', 500),
        ])->assertRedirect();
        $this->assertDatabaseCount('posts', 1);

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'content' => str_repeat('a', 501),
        ]);

        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('posts', 1);
    }

    public function test_user_can_toggle_a_like_on_a_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
        ]);

        $this->actingAs($user)
            ->postJson(route('comments.like', $comment))
            ->assertOk()
            ->assertExactJson(['liked' => true, 'count' => 1]);
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);

        $this->postJson(route('comments.like', $comment))
            ->assertOk()
            ->assertExactJson(['liked' => false, 'count' => 0]);
        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_interactions_notify_content_owners_but_not_the_actor(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $replier = User::factory()->create();
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'A post',
        ]);

        $comment = $this->actingAs($commenter)->postJson("/posts/{$post->id}/comments", [
            'content' => 'A comment',
        ])->assertOk()->json('comment');

        $this->actingAs($replier)->postJson("/posts/{$post->id}/comments", [
            'content' => 'A reply',
            'parent_id' => $comment['id'],
        ])->assertOk();
        $this->postJson(route('posts.like', $post))->assertOk();
        $this->postJson(route('comments.like', $comment['id']))->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'sender_id' => $commenter->id,
            'post_id' => $post->id,
            'message' => $commenter->name.' mengomentari postingan Anda.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $commenter->id,
            'sender_id' => $replier->id,
            'post_id' => $post->id,
            'message' => $replier->name.' membalas komentar Anda.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'sender_id' => $replier->id,
            'post_id' => $post->id,
            'message' => $replier->name.' menyukai postingan Anda.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $commenter->id,
            'sender_id' => $replier->id,
            'post_id' => $post->id,
            'message' => $replier->name.' menyukai komentar Anda.',
        ]);

        $this->actingAs($owner)->postJson(route('posts.like', $post))->assertOk();
        $this->actingAs($commenter)->postJson(route('comments.like', $comment['id']))->assertOk();
        $this->assertDatabaseCount('notifications', 4);
    }

    public function test_notification_menu_shows_unread_count_and_only_recipient_can_mark_read(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();
        $post = Post::create([
            'user_id' => $recipient->id,
            'content' => 'A post',
        ]);
        $notification = Notification::create([
            'user_id' => $recipient->id,
            'sender_id' => $sender->id,
            'post_id' => $post->id,
            'message' => 'Sender menyukai postingan Anda.',
        ]);
        $otherUserNotification = Notification::create([
            'user_id' => $sender->id,
            'sender_id' => $recipient->id,
            'post_id' => $post->id,
            'message' => 'Recipient mengomentari postingan Anda.',
        ]);

        $this->actingAs($recipient)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sender menyukai postingan Anda.')
            ->assertSee('id="post-'.$post->id.'"', false)
            ->assertSee('Notifikasi, 1 belum dibaca');

        $this->post(route('notifications.read', $notification))
            ->assertRedirect(route('dashboard').'#post-'.$post->id);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => true]);

        $this->post(route('notifications.read', $otherUserNotification))->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $otherUserNotification->id, 'is_read' => false]);
    }

    public function test_dashboard_and_profile_render_likes_for_comments_and_replies(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
        ]);
        $reply = Comment::create([
            'post_id' => $post->id,
            'user_id' => $otherUser->id,
            'parent_id' => $comment->id,
            'body' => 'A reply',
        ]);
        $nestedReply = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $reply->id,
            'body' => 'A nested reply',
        ]);
        $comment->likes()->attach($user->id);
        $reply->likes()->attach($otherUser->id);
        $nestedReply->likes()->attach($user->id);

        $dashboard = $this->actingAs($user)->get(route('dashboard'));
        $profile = $this->get(route('profile.show', $user));

        foreach ([$dashboard, $profile] as $response) {
            $response->assertOk()
                ->assertSee('comment-like-button-'.$comment->id, false)
                ->assertSee('comment-like-count-'.$comment->id, false)
                ->assertSee('comment-like-button-'.$reply->id, false)
                ->assertSee('comment-like-count-'.$reply->id, false)
                ->assertSee('id="reply-form-'.$comment->id.'"', false)
                ->assertSee('id="reply-input-'.$comment->id.'"', false)
                ->assertSee('id="reply-form-'.$reply->id.'"', false)
                ->assertSee('id="reply-input-'.$reply->id.'"', false)
                ->assertSee('A nested reply')
                ->assertSee('comment-like-button-'.$nestedReply->id, false)
                ->assertSee('comment-like-count-'.$nestedReply->id, false)
                ->assertSee('id="reply-form-'.$nestedReply->id.'"', false)
                ->assertSee('id="reply-input-'.$nestedReply->id.'"', false);

            $this->assertSame(1, substr_count($response->getContent(), 'A nested reply'));
        }

        $dashboard->assertSee('aria-pressed="true"', false);
    }

    public function test_post_and_comment_locations_are_stored_only_after_opt_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'content' => 'Without location consent',
            'location' => 'Bandung',
        ])->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'content' => 'Without location consent',
            'location' => null,
        ]);

        $this->post(route('posts.store'), [
            'content' => 'With location consent',
            'share_location' => '1',
            'location' => 'Bandung',
        ])->assertRedirect();
        $post = Post::where('content', 'With location consent')->firstOrFail();
        $this->assertSame('Bandung', $post->location);

        $this->postJson("/posts/{$post->id}/comments", [
            'content' => 'Without location consent',
            'location' => 'Bandung',
        ])->assertOk()->assertJsonPath('comment.location', null);

        $this->postJson("/posts/{$post->id}/comments", [
            'content' => 'With location consent',
            'share_location' => '1',
            'location' => 'Bandung',
        ])->assertOk()->assertJsonPath('comment.location', 'Bandung');

        $this->get(route('dashboard'))->assertSee('Bandung');
        $this->get(route('profile.show', $user->id))->assertSee('Bandung');
    }

    public function test_user_can_attach_a_photo_to_a_comment(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'content' => '',
            'image' => UploadedFile::fake()->createWithContent(
                'comment.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
            ),
        ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('success', true);
        $comment = Comment::where('post_id', $post->id)->firstOrFail();

        $this->assertNull($comment->body);
        $this->assertNotNull($comment->image_path);
        Storage::disk('public')->assertExists($comment->image_path);
    }

    public function test_comment_requires_either_text_or_a_photo(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);

        $response = $this->actingAs($user)->postJson("/posts/{$post->id}/comments", [
            'content' => '',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['content', 'image']);
        $this->assertDatabaseMissing('comments', ['post_id' => $post->id]);
    }

    public function test_comment_rejects_non_image_uploads(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'content' => 'Not an image',
            'image' => UploadedFile::fake()->create('comment.txt', 10, 'text/plain'),
        ], ['Accept' => 'application/json']);

        $response->assertUnprocessable()->assertJsonValidationErrors('image');
        $this->assertDatabaseMissing('comments', [
            'post_id' => $post->id,
            'body' => 'Not an image',
        ]);
    }

    public function test_deleting_a_comment_removes_its_photo_and_nested_reply_photos(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $commentImage = 'comments/comment.jpg';
        $replyImage = 'comments/reply.jpg';
        Storage::disk('public')->put($commentImage, 'comment');
        Storage::disk('public')->put($replyImage, 'reply');
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
            'image_path' => $commentImage,
        ]);
        $reply = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'body' => 'A reply',
            'image_path' => $replyImage,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('comments.destroy', $comment));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
        Storage::disk('public')->assertMissing([$commentImage, $replyImage]);
    }

    public function test_user_cannot_delete_another_users_comment_photo(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'A post',
        ]);
        $imagePath = 'comments/owned.jpg';
        Storage::disk('public')->put($imagePath, 'photo');
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
            'body' => 'An owned comment',
            'image_path' => $imagePath,
        ]);

        $response = $this->actingAs($otherUser)->deleteJson(route('comments.destroy', $comment));

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_reply_route_creates_a_reply_to_the_selected_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $parent = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
        ]);

        Storage::fake('public');
        $response = $this->actingAs($user)->post("/comments/{$parent->id}/reply", [
            'content' => '',
            'image' => UploadedFile::fake()->createWithContent(
                'reply.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
            ),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('comment.parent_id', $parent->id);
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'body' => null,
        ]);
        $reply = Comment::where('parent_id', $parent->id)->firstOrFail();
        $this->assertNotNull($reply->image_path);
        Storage::disk('public')->assertExists($reply->image_path);

        foreach ([
            $this->get(route('dashboard')),
            $this->get(route('profile.show', $user)),
        ] as $page) {
            $page->assertOk()
                ->assertSee('class="d-block fw-bold text-dark">'.$user->name.'</span>', false)
                ->assertSee('class="d-block mt-1 mb-2 img-fluid rounded-3"', false);
        }
    }

    public function test_comment_cannot_reply_to_a_comment_on_another_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $otherPost = Post::create([
            'user_id' => $user->id,
            'content' => 'Another post',
        ]);
        $parent = Comment::create([
            'post_id' => $otherPost->id,
            'user_id' => $user->id,
            'body' => 'A comment on another post',
        ]);

        $response = $this->actingAs($user)->postJson("/posts/{$post->id}/comments", [
            'content' => 'A misplaced reply',
            'parent_id' => $parent->id,
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('comments', [
            'post_id' => $post->id,
            'body' => 'A misplaced reply',
        ]);
    }

    public function test_dashboard_renders_comment_deletion_controls(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
            'image_path' => 'comments/photo.png',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('onclick="confirmDeleteComment', false)
            ->assertSee('comment-image-input', false)
            ->assertSee('storage/comments/photo.png', false);
    }

    public function test_profile_renders_comment_photo(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
            'image_path' => 'comments/profile-photo.png',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show', $user));

        $response->assertOk()->assertSee('storage/comments/profile-photo.png', false);
    }

    public function test_dashboard_and_profile_show_the_reply_timestamp(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'A post',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'A comment',
        ]);
        $comment->created_at = '2026-11-10 10:00:00';
        $comment->save();
        $reply = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'body' => 'A reply',
        ]);
        $reply->created_at = '2026-11-11 12:00:00';
        $reply->save();

        $dashboard = $this->actingAs($user)->get(route('dashboard'));
        $profile = $this->get(route('profile.show', $user));

        $dashboard->assertOk()->assertSee('11 Nov 2026, 12:00');
        $profile->assertOk()->assertSee('11 Nov 2026, 12:00');
    }

    public function test_dashboard_shows_profile_cards_for_all_users(): void
    {
        $viewer = User::factory()->create();
        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'bio' => 'A public bio',
        ]);
        Post::create([
            'user_id' => $viewer->id,
            'content' => 'My post',
        ]);
        Post::create([
            'user_id' => $otherUser->id,
            'content' => 'Their post',
        ]);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('aria-label="Lihat profil Other User"', false)
            ->assertSee('A public bio')
            ->assertSee('aria-label="Lihat profil '.$viewer->name.'"', false);
    }
}
