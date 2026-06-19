<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(10)->create();

        $users[] = User::factory()->create([
            "name" => "Admin",
            "email" => "admin@example.com",
            "password" => "123123123",
        ]);

        $tags = Tag::factory(5)->create();
        $products = Product::factory(10)->create();
        $comments = Comment::factory(50)
            ->state(
                fn() => [
                    "commentable_id" => $products->random()->id,
                    "commentable_type" => Product::class,
                ],
            )
            ->create();

        foreach ($products as $product) {
            $product->tags()->attach($tags->random(rand(1, 3))->pluck("id"));
        }

        $users->last()->attach($products->first());
    }
}
