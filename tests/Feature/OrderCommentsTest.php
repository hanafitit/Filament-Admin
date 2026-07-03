<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderComment;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Tests\TestCase;

class OrderCommentsTest extends TestCase
{
    public function test_order_comments_are_internal_by_default(): void
    {
        $order = $this->makeOrder();

        $comment = OrderComment::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'content' => 'Обсудить детали с командой',
        ]);

        $this->assertSame(OrderComment::TYPE_COMMENT, $comment->fresh()->type);
        $this->assertTrue($comment->fresh()->is_internal);
    }

    public function test_order_comment_can_be_saved_as_internal_note(): void
    {
        $order = $this->makeOrder();

        $comment = OrderComment::query()->create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => OrderComment::TYPE_INTERNAL_NOTE,
            'content' => 'Не показывать клиенту: себестоимость и риски.',
            'is_internal' => true,
        ]);

        $this->assertSame(OrderComment::TYPE_INTERNAL_NOTE, $comment->fresh()->type);
        $this->assertTrue($comment->fresh()->is_internal);
    }

    protected function makeOrder(): Order
    {
        $source = Source::query()->create([
            'name' => 'Direct',
            'slug' => 'direct',
        ]);
        $status = Status::query()->create([
            'name' => 'Новый',
            'color' => 'primary',
            'sort_order' => 1,
        ]);
        $user = User::factory()->create();

        return Order::query()->create([
            'title' => 'Test order',
            'source_id' => $source->id,
            'status_id' => $status->id,
            'user_id' => $user->id,
            'budget' => 1000,
            'commission' => 0,
            'deadline' => now()->addDay(),
        ]);
    }
}
