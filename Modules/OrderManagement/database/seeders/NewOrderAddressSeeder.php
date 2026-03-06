<?php

namespace Modules\OrderManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\OrderAddress;

class NewOrderAddressSeeder extends Seeder
{
   public function run()
    {
        $order = Order::pluck('id')->toArray();

        OrderAddress::insert([
            ['order_id' =>  $order[0], 'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com', 'phone' => '1234567890', 'address_line1' => '123 Main St', 'city' => 'New York', 'state' => 'NY', 'postal_code' => '10001', 'country' => 'USA', 'type' => 'shipping', 'is_default' => true],
            ['order_id' =>  $order[1], 'first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@example.com', 'phone' => '0987654321', 'address_line1' => '456 Oak St', 'city' => 'Los Angeles', 'state' => 'CA', 'postal_code' => '90001', 'country' => 'USA', 'type' => 'billing', 'is_default' => true],
            ['order_id' =>  $order[2], 'first_name' => 'Alice', 'last_name' => 'Johnson', 'email' => 'alice@example.com', 'phone' => '2345678901', 'address_line1' => '789 Pine St', 'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601', 'country' => 'USA', 'type' => 'shipping', 'is_default' => false],
            ['order_id' =>  $order[3], 'first_name' => 'Bob', 'last_name' => 'Brown', 'email' => 'bob@example.com', 'phone' => '3456789012', 'address_line1' => '101 Maple St', 'city' => 'San Francisco', 'state' => 'CA', 'postal_code' => '94101', 'country' => 'USA', 'type' => 'shipping', 'is_default' => true],
            ['order_id' =>  $order[4], 'first_name' => 'Charlie', 'last_name' => 'Davis', 'email' => 'charlie@example.com', 'phone' => '4567890123', 'address_line1' => '202 Birch St', 'city' => 'Houston', 'state' => 'TX', 'postal_code' => '77001', 'country' => 'USA', 'type' => 'billing', 'is_default' => false]
        ]);
    }
}
