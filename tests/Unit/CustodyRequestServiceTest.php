<?php

namespace Tests\Unit;

use App\Domain\Custody\CustodyRequestService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustodyRequestServiceTest extends TestCase
{
    private CustodyRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('CustodyRequestItems');
        Schema::dropIfExists('CustodyRequests');

        Schema::create('CustodyRequests', function (Blueprint $table) {
            $table->increments('RequestID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->unsignedInteger('EventTypeID')->nullable();
            $table->date('DateFrom');
            $table->date('DateTo');
            $table->string('Status')->default('pending');
            $table->text('UserNote')->nullable();
            $table->text('AdminNote')->nullable();
            $table->unsignedInteger('ReviewedBy')->nullable();
            $table->timestamp('ReviewedAt')->nullable();
            $table->timestamps();
        });

        Schema::create('CustodyRequestItems', function (Blueprint $table) {
            $table->increments('RequestItemID');
            $table->unsignedInteger('RequestID');
            $table->unsignedInteger('InventoryID');
            $table->string('ItemNameSnapshot');
            $table->string('ItemUnitSnapshot')->nullable();
            $table->unsignedInteger('QtyRequested');
            $table->unsignedInteger('QtyApproved')->nullable();
            $table->text('AdminItemNote')->nullable();
            $table->timestamps();
        });

        $this->service = new CustodyRequestService();
    }

    public function test_create_inserts_request_and_items_in_transaction(): void
    {
        $requestId = $this->service->create(
            10,
            '2026-01-21',
            '2026-01-22',
            1,
            2,
            'Need items',
            [
                [
                    'InventoryID' => 5,
                    'ItemNameSnapshot' => 'Tent',
                    'ItemUnitSnapshot' => 'piece',
                    'QtyRequested' => 2,
                ],
                [
                    'InventoryID' => 7,
                    'ItemNameSnapshot' => 'Rope',
                    'ItemUnitSnapshot' => 'meter',
                    'QtyRequested' => 10,
                ],
            ]
        );

        $this->assertSame(1, $requestId);

        $request = DB::table('CustodyRequests')->where('RequestID', $requestId)->first();
        $this->assertNotNull($request);
        $this->assertSame(10, (int) $request->PersonID);
        $this->assertSame(1, (int) $request->QetaaID);
        $this->assertSame(2, (int) $request->EventTypeID);
        $this->assertSame('2026-01-21', $request->DateFrom);
        $this->assertSame('2026-01-22', $request->DateTo);
        $this->assertSame('pending', $request->Status);
        $this->assertSame('Need items', $request->UserNote);

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $requestId)
            ->orderBy('InventoryID')
            ->get();

        $this->assertCount(2, $items);
        $this->assertSame(5, (int) $items[0]->InventoryID);
        $this->assertSame('Tent', $items[0]->ItemNameSnapshot);
        $this->assertSame(2, (int) $items[0]->QtyRequested);
        $this->assertSame(7, (int) $items[1]->InventoryID);
        $this->assertSame(10, (int) $items[1]->QtyRequested);
    }

    public function test_update_pending_replaces_items(): void
    {
        $requestId = $this->service->create(10, '2026-01-21', '2026-01-22', null, null, 'old', [
            [
                'InventoryID' => 5,
                'ItemNameSnapshot' => 'Tent',
                'ItemUnitSnapshot' => 'piece',
                'QtyRequested' => 2,
            ],
        ]);

        $this->service->updatePending($requestId, 10, '2026-02-01', '2026-02-03', 1, 2, 'new', [
            [
                'InventoryID' => 9,
                'ItemNameSnapshot' => 'Rope',
                'ItemUnitSnapshot' => 'm',
                'QtyRequested' => 4,
            ],
        ]);

        $request = DB::table('CustodyRequests')->where('RequestID', $requestId)->first();
        $this->assertSame('2026-02-01', $request->DateFrom);
        $this->assertSame('new', $request->UserNote);

        $items = DB::table('CustodyRequestItems')->where('RequestID', $requestId)->get();
        $this->assertCount(1, $items);
        $this->assertSame(9, (int) $items[0]->InventoryID);
        $this->assertSame(4, (int) $items[0]->QtyRequested);
    }

    public function test_delete_pending_removes_request(): void
    {
        $requestId = $this->service->create(10, '2026-01-21', '2026-01-22', null, null, null, [
            [
                'InventoryID' => 5,
                'ItemNameSnapshot' => 'Tent',
                'ItemUnitSnapshot' => 'piece',
                'QtyRequested' => 1,
            ],
        ]);

        $this->service->deletePending($requestId, 10);

        $this->assertNull(DB::table('CustodyRequests')->where('RequestID', $requestId)->first());
    }
}
