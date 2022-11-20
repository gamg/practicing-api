<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_authenticated_users_cannot_access_the_following_endpoints_for_the_product_api()
    {
        $index = $this->json('GET', '/api/products');
        $index->assertStatus(401);

        $store = $this->json('POST', '/api/products');
        $store->assertStatus(401);

        $show = $this->json('GET', '/api/products/-1');
        $show->assertStatus(401);

        $update = $this->putJson('/api/products/-1');
        $update->assertStatus(401);

        $destroy = $this->deleteJson('/api/products/-1');
        $destroy->assertStatus(401);
    }

    /** @test */
    public function can_return_a_collection_of_paginated_products()
    {
        $product1 = $this->create(Product::class);
        $product2 = $this->create(Product::class);
        $product3 = $this->create(Product::class);

        $response = $this->actingAs($this->create(User::class), 'api')->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id' ,'name', 'slug', 'price', 'created_at']
                ],
                //'links' => ['first', 'last', 'prev', 'next'],
                //'meta' => ['current_page', 'last_page', 'from', 'to', 'path', 'per_page', 'total']
            ]);
    }

    /** @test */
    public function can_create_a_product()
    {
        $faker = Factory::create();
        // Given
        /// user is authenticated

        // When
        /// post request create product
        $response = $this->actingAs($this->create(User::class), 'api')->postJson('/api/products', [
            'name' => $name = $faker->company,
            'price' => $price = random_int(10, 100)
        ]);
        //$this->markTestIncomplete('message');
        //$this->markTestSkipped('messages');

        // Then
        /// product exists
        $response->assertJsonStructure(['data' => ['id' ,'name', 'slug', 'price', 'created_at']])
                ->assertJson(['data' => [
                    'name' => $name,
                    'slug' => Str::of($name)->slug(),
                    'price' => $price
                ]])->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name'  => $name,
            'slug'  => Str::of($name)->slug(),
            'price' => $price
        ]);
    }

    /** @test */
    public function will_fail_with_a_404_if_product_is_not_found()
    {
        $response = $this->actingAs($this->create(User::class), 'api')->getJson('api/products/-1');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_return_a_product()
    {
        // Given
        $product = $this->create(Product::class);

        //When
        $response = $this->actingAs($this->create(User::class), 'api')->json('GET', "api/products/$product->id");

        // Then
        // here I use ['data' =>[]] because of the API resource
        $response->assertStatus(200)
            ->assertExactJson(['data' => [
                'id'   => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'created_at' => (string) $product->created_at
            ]]);
    }

    /** @test */
    public function will_fail_with_a_404_if_product_we_want_to_update_is_not_found()
    {
        $response = $this->actingAs($this->create(User::class), 'api')->putJson('api/products/-1');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_update_a_product()
    {
        $product = $this->create(Product::class);

        $response = $this->actingAs($this->create(User::class), 'api')->json('PUT', "api/products/{$product->id}", [
            'name' => 'My new nice name',
            'price' => 77
        ]);

        $product->refresh();

        $response->assertStatus(200)
            ->assertExactJson(['data' => [
                'id'   => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'created_at' => (string) $product->created_at
            ]]);

        $this->assertDatabaseHas('products', [
            'id'   => $product->id,
            'name'  => 'My new nice name',
            'slug'  => Str::of('My new nice name')->slug(),
            'price' => 77
        ]);
    }

    /** @test */
    public function will_fail_with_a_404_if_product_we_want_to_delete_is_not_found()
    {
        $response = $this->actingAs($this->create(User::class), 'api')->deleteJson('api/products/-1');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_delete_a_product()
    {
        $product = $this->create(Product::class);

        $response = $this->actingAs($this->create(User::class), 'api')->deleteJson("api/products/{$product->id}");

        $response->assertStatus(204)->assertSee(null);

        $this->assertDatabaseMissing('products', [
            'id'   => $product->id,
        ]);
    }
}
