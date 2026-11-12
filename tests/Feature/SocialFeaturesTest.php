<?php

namespace Tests\Feature;

use App\Models\Comment;
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

    public function test_dashboard_shows_profile_cards_only_for_other_users(): void
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
            ->assertDontSee('aria-label="Lihat profil ' . $viewer->name . '"', false);
    }
}
