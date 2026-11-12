<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->actingAs($user)->postJson("/comments/{$parent->id}/reply", [
            'content' => 'A reply',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('comment.parent_id', $parent->id);
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'body' => 'A reply',
        ]);
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
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('confirmDeleteComment', false)
            ->assertSee('function escapeHtml(value)', false);
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
            ->assertDontSee('aria-label="Lihat profil '.$viewer->name.'"', false);
    }
}
