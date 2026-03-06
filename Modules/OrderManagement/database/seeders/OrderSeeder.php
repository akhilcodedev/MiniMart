<?php

namespace Modules\OrderManagement\Database\Seeders;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;



class OrderSeeder extends Seeder
{
    public function run()
    {    
        DB::statement("INSERT INTO new_payment_status_options (status, label, created_at, updated_at) VALUES
        ('paid', 'Paid', NOW(), NOW()),
        ('pending', 'Pending', NOW(), NOW()),
        ('failed', 'Failed', NOW(), NOW()),
        ('refunded', 'Refunded', NOW(), NOW()),
        ('processing', 'Processing', NOW(), NOW()),
        ('completed', 'Completed', NOW(), NOW()),
        ('on-hold', 'On Hold', NOW(), NOW()),
        ('canceled', 'Canceled', NOW(), NOW()),
        ('disputed', 'Disputed', NOW(), NOW()),
        ('reversed', 'Reversed', NOW(), NOW());");

        // DB::statement("INSERT INTO new_orders (order_number, customer_id, customer_code, first_name, last_name, email, phone, payment_status, created_at, updated_at) VALUES
        // ('ORD001', 1, 'CUST001', 'John', 'Doe', 'john.doe@example.com', '1234567890', 1, NOW(), NOW()),
        // ('ORD002', 2, 'CUST002', 'Jane', 'Smith', 'jane.smith@example.com', '0987654321', 2, NOW(), NOW()),
        // ('ORD003', 3, 'CUST003', 'Alice', 'Johnson', 'alice.johnson@example.com', '1234567891', 3, NOW(), NOW()),
        // ('ORD004', 4, 'CUST004', 'Bob', 'Brown', 'bob.brown@example.com', '1234567892', 4, NOW(), NOW()),
        // ('ORD005', 5, 'CUST005', 'Charlie', 'Davis', 'charlie.davis@example.com', '1234567893', 5, NOW(), NOW()),
        // ('ORD006', 6, 'CUST006', 'Eve', 'Miller', 'eve.miller@example.com', '1234567894', 6, NOW(), NOW()),
        // ('ORD007', 7, 'CUST007', 'Frank', 'Wilson', 'frank.wilson@example.com', '1234567895', 7, NOW(), NOW()),
        // ('ORD008', 8, 'CUST008', 'Grace', 'Lee', 'grace.lee@example.com', '1234567896', 8, NOW(), NOW()),
        // ('ORD009', 9, 'CUST009', 'Hank', 'Taylor', 'hank.taylor@example.com', '1234567897', 9, NOW(), NOW()),
        // ('ORD010', 10, 'CUST010', 'Ivy', 'Anderson', 'ivy.anderson@example.com', '1234567898', 10, NOW(), NOW());");

        // DB::statement("INSERT INTO new_order_addresses (order_id, first_name, last_name, email, phone, address_line1, city, state, postal_code, country, type, is_default, created_at, updated_at) VALUES
        // (1, 'John', 'Doe', 'john.doe@example.com', '1234567890', '123 Main St', 'New York', 'NY', '10001', 'USA', 'shipping', true, NOW(), NOW()),
        // (2, 'Jane', 'Smith', 'jane.smith@example.com', '0987654321', '456 Oak St', 'Los Angeles', 'CA', '90001', 'USA', 'billing', false, NOW(), NOW()),
        // (3, 'Alice', 'Johnson', 'alice.johnson@example.com', '1234567891', '789 Pine St', 'Chicago', 'IL', '60601', 'USA', 'shipping', true, NOW(), NOW()),
        // (4, 'Bob', 'Brown', 'bob.brown@example.com', '1234567892', '101 Maple St', 'San Francisco', 'CA', '94101', 'USA', 'shipping', false, NOW(), NOW()),
        // (5, 'Charlie', 'Davis', 'charlie.davis@example.com', '1234567893', '202 Birch St', 'Houston', 'TX', '77001', 'USA', 'billing', true, NOW(), NOW()),
        // (6, 'Eve', 'Miller', 'eve.miller@example.com', '1234567894', '303 Cedar St', 'Seattle', 'WA', '98101', 'USA', 'shipping', false, NOW(), NOW()),
        // (7, 'Frank', 'Wilson', 'frank.wilson@example.com', '1234567895', '404 Spruce St', 'Denver', 'CO', '80201', 'USA', 'billing', true, NOW(), NOW()),
        // (8, 'Grace', 'Lee', 'grace.lee@example.com', '1234567896', '505 Elm St', 'Austin', 'TX', '73301', 'USA', 'shipping', false, NOW(), NOW()),
        // (9, 'Hank', 'Taylor', 'hank.taylor@example.com', '1234567897', '606 Walnut St', 'Boston', 'MA', '02101', 'USA', 'billing', true, NOW(), NOW()),
        // (10, 'Ivy', 'Anderson', 'ivy.anderson@example.com', '1234567898', '707 Ash St', 'Miami', 'FL', '33101', 'USA', 'shipping', false, NOW(), NOW());");

        // DB::statement("INSERT INTO new_order_items (cart_id, product_id, product_name, product_price, quantity, total, created_at, updated_at) VALUES
        // (1, 1, 'Product A', 50.00, 2, 100.00, NOW(), NOW()),
        // (2, 2, 'Product B', 30.00, 1, 30.00, NOW(), NOW()),
        // (3, 3, 'Product C', 20.00, 3, 60.00, NOW(), NOW()),
        // (4, 4, 'Product D', 15.00, 1, 15.00, NOW(), NOW()),
        // (5, 5, 'Product E', 25.00, 4, 100.00, NOW(), NOW()),
        // (6, 6, 'Product F', 40.00, 1, 40.00, NOW(), NOW()),
        // (7, 7, 'Product G', 10.00, 5, 50.00, NOW(), NOW()),
        // (8, 8, 'Product H', 35.00, 2, 70.00, NOW(), NOW()),
        // (9, 9, 'Product I', 45.00, 3, 135.00, NOW(), NOW()),
        // (10, 10, 'Product J', 55.00, 1, 55.00, NOW(), NOW());");

        // DB::statement("INSERT INTO new_order_comments (order_id, comment, commented_by, created_at, updated_at) VALUES
        // (1, 'Order shipped successfully.', 'admin', NOW(), NOW()),
        // (2, 'Payment pending.', 'customer', NOW(), NOW()),
        // (3, 'Order delivered.', 'admin', NOW(), NOW()),
        // (4, 'Awaiting shipment.', 'customer', NOW(), NOW()),
        // (5, 'Order canceled.', 'admin', NOW(), NOW()),
        // (6, 'Order refunded.', 'customer', NOW(), NOW()),
        // (7, 'Shipping delayed.', 'admin', NOW(), NOW()),
        // (8, 'Order processing.', 'customer', NOW(), NOW()),
        // (9, 'Item out of stock.', 'admin', NOW(), NOW()),
        // (10, 'Order on hold.', 'customer', NOW(), NOW());");

    }
}
