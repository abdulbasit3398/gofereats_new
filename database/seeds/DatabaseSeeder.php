<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run() {
		$this->call([
			SiteSettingTableSeeder::class,			
			TimezoneTableSeeder::class,
			LaravelEntrustSeeder::class,
			VehicleTypeTableSeeder::class,
			FoodReceiverTableSeeder::class,
			OrderCancelReasonTableSeeder::class,
			IssueTypeTableSeeder::class,
			HomeSliderTableSeeder::class,
			CuisineTableSeeder::class,
			LanguageTableSeeder::class,
			CountryTableSeeder::class,
			StaticPagesTableSeeder::class,
			CurrencyTableSeeder::class,
			ServiceTypeTableSeeder::class,
			FileTypeTableSeeder::class,
			FileTableSeeder::class,
			HelpCategoryTableSeeder::class,
			HelpSubCategoryTableSeeder::class,
			HelpTableSeeder::class,
			SupportSeeder::class,
		]);
	}
}
