<?php

namespace Modules\PriceRuleManagement\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\PriceRuleManagement\Models\CouponEntity;
use Modules\PriceRuleManagement\Models\CouponMode;
use Modules\PriceRuleManagement\Models\CouponType;
use Illuminate\Support\Facades\Schema;

class CouponRelatedTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        $defAdmin = User::select('*')->orderBy('id', 'asc')->first();

        $availableCouponTypes = $this->getDefaultCouponTypes();
        if (count($availableCouponTypes) > 0) {
            /*Schema::disableForeignKeyConstraints();
            CouponType::query()->truncate();*/
            foreach ($availableCouponTypes as $typeEL) {
                $insertedType = CouponType::updateOrCreate([
                    'code' => $typeEL['code']
                ], [
                    'name' => $typeEL['name'],
                    'sort_order' => $typeEL['sort_order'],
                    'is_active' => $typeEL['is_active'],
                    'created_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                    'updated_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                ]);
            }
            /*Schema::enableForeignKeyConstraints();*/
            $this->command->info('Seeded the Default Coupon Types!');
        }

        $availableCouponModes = $this->getDefaultCouponModes();
        if (count($availableCouponModes) > 0) {
            /*Schema::disableForeignKeyConstraints();
            CouponMode::query()->truncate();*/
            foreach ($availableCouponModes as $modeEL) {
                $insertedMode = CouponMode::updateOrCreate([
                    'code' => $modeEL['code']
                ], [
                    'name' => $modeEL['name'],
                    'sort_order' => $modeEL['sort_order'],
                    'is_active' => $modeEL['is_active'],
                    'created_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                    'updated_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                ]);
            }
            /*Schema::enableForeignKeyConstraints();*/
            $this->command->info('Seeded the Default Coupon Modes!');
        }

        $availableCouponApplications = $this->getDefaultCouponEntities();
        if (count($availableCouponApplications) > 0) {
            /*Schema::disableForeignKeyConstraints();
            CouponEntity::query()->truncate();*/
            foreach ($availableCouponApplications as $applicationEL) {
                $insertedApplication = CouponEntity::updateOrCreate([
                    'code' => $applicationEL['code']
                ], [
                    'name' => $applicationEL['name'],
                    'sort_order' => $applicationEL['sort_order'],
                    'is_active' => $applicationEL['is_active'],
                    'created_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                    'updated_by' => (!is_null($defAdmin)) ? $defAdmin->id : null,
                ]);
            }
            /*Schema::enableForeignKeyConstraints();*/
            $this->command->info('Seeded the Default Coupon Entities!');
        }

    }

    /**
     * Returns default fixed Coupon Types which must be present for accessing the system.
     *
     * @return array
     */
    private function getDefaultCouponTypes(): array
    {
        return [
            'manual' => [
                'code' => 'manual',
                'name' => 'Manual Type',
                'sort_order' => 1,
                'is_active' => 1
            ],
            'promotional' => [
                'code' => 'promotional',
                'name' => 'Promotional',
                'sort_order' => 2,
                'is_active' => 1
            ],
            'inaugural' => [
                'code' => 'inaugural',
                'name' => 'Inaugural',
                'sort_order' => 3,
                'is_active' => 1
            ],
            'first_time_user' => [
                'code' => 'first_time_user',
                'name' => 'First Time (User)',
                'sort_order' => 4,
                'is_active' => 1
            ],
            'cashback' => [
                'code' => 'cashback',
                'name' => 'Cashback',
                'sort_order' => 5,
                'is_active' => 1
            ],
        ];
    }

    /**
     * Returns default fixed Coupon Modes which must be present for accessing the system.
     *
     * @return array
     */
    private function getDefaultCouponModes(): array
    {
        return [
            'amount' => [
                'code' => 'amount',
                'name' => 'Amount Discount',
                'sort_order' => 1,
                'is_active' => 1
            ],
            'percentage' => [
                'code' => 'percentage',
                'name' => 'Percent Discount',
                'sort_order' => 2,
                'is_active' => 1
            ],
        ];
    }

    /**
     * Returns default fixed Coupon Entities which must be present for accessing the system.
     *
     * @return array
     */
    private function getDefaultCouponEntities(): array
    {
        return [
            'all' => [
                'code' => 'all',
                'name' => 'All',
                'sort_order' => 1,
                'is_active' => 1
            ],
            'category' => [
                'code' => 'category',
                'name' => 'Category',
                'sort_order' => 2,
                'is_active' => 1
            ],
            'product' => [
                'code' => 'product',
                'name' => 'Product',
                'sort_order' => 3,
                'is_active' => 1
            ],
        ];
    }

}
