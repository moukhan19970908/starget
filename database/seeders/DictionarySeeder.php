<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\LoadingType;
use App\Models\RefusalReason;
use App\Models\StopType;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        // Cities
        $cities = [
            ['name' => 'Алматы', 'country' => 'KZ'],
            ['name' => 'Астана', 'country' => 'KZ'],
            ['name' => 'Шымкент', 'country' => 'KZ'],
            ['name' => 'Карагандa', 'country' => 'KZ'],
            ['name' => 'Актобе', 'country' => 'KZ'],
            ['name' => 'Тараз', 'country' => 'KZ'],
            ['name' => 'Павлодар', 'country' => 'KZ'],
            ['name' => 'Усть-Каменогорск', 'country' => 'KZ'],
            ['name' => 'Семей', 'country' => 'KZ'],
            ['name' => 'Атырау', 'country' => 'KZ'],
            ['name' => 'Актау', 'country' => 'KZ'],
            ['name' => 'Кокшетау', 'country' => 'KZ'],
            ['name' => 'Талдыкорган', 'country' => 'KZ'],
            ['name' => 'Туркестан', 'country' => 'KZ'],
            ['name' => 'Москва', 'country' => 'RU'],
            ['name' => 'Санкт-Петербург', 'country' => 'RU'],
            ['name' => 'Новосибирск', 'country' => 'RU'],
            ['name' => 'Берлин', 'country' => 'DE'],
            ['name' => 'Варшава', 'country' => 'PL'],
            ['name' => 'Шанхай', 'country' => 'CN'],
        ];
        foreach ($cities as $c) {
            City::firstOrCreate(['name' => $c['name'], 'country' => $c['country']]);
        }

        // Currencies
        $currencies = [
            ['code' => 'KZT', 'name' => 'Казахстанский тенге'],
            ['code' => 'USD', 'name' => 'Доллар США'],
            ['code' => 'EUR', 'name' => 'Евро'],
            ['code' => 'RUB', 'name' => 'Российский рубль'],
        ];
        foreach ($currencies as $c) {
            Currency::firstOrCreate(['code' => $c['code']], $c);
        }

        // Loading types
        $loadingTypes = ['Задняя', 'Боковая', 'Верхняя', 'Задняя+Боковая'];
        foreach ($loadingTypes as $lt) {
            LoadingType::firstOrCreate(['name' => $lt]);
        }

        // Vehicle types
        $vehicleTypes = [
            ['name' => 'Тентованный', 'is_ref' => false],
            ['name' => 'Реф', 'is_ref' => true],
            ['name' => 'Рефрижератор', 'is_ref' => true],
            ['name' => 'Бортовой', 'is_ref' => false],
            ['name' => 'Изотермический', 'is_ref' => false],
            ['name' => 'Контейнеровоз', 'is_ref' => false],
            ['name' => 'Самосвал', 'is_ref' => false],
            ['name' => 'Цистерна', 'is_ref' => false],
        ];
        foreach ($vehicleTypes as $vt) {
            VehicleType::firstOrCreate(['name' => $vt['name']], $vt);
        }

        // Document types
        $documentTypes = [
            'Водительское удостоверение',
            'Паспорт',
            'Технический паспорт',
            'СТС (Свидетельство о регистрации)',
            'Удостоверение личности',
            'Справка о несудимости',
        ];
        foreach ($documentTypes as $dt) {
            DocumentType::firstOrCreate(['name' => $dt]);
        }

        // Stop types
        $stopTypes = ['Погрузка', 'Выгрузка', 'Промежуточная'];
        foreach ($stopTypes as $st) {
            StopType::firstOrCreate(['name' => $st]);
        }

        // Refusal reasons
        $refusalReasons = [
            ['name' => 'Клиент изменил планы', 'type' => 'client'],
            ['name' => 'Клиент нашёл другого перевозчика', 'type' => 'client'],
            ['name' => 'Нет подходящего транспорта', 'type' => 'our_side'],
            ['name' => 'Не можем обеспечить маршрут', 'type' => 'our_side'],
            ['name' => 'Взаимное соглашение', 'type' => 'mutual'],
            ['name' => 'Изменение условий договора', 'type' => 'mutual'],
        ];
        foreach ($refusalReasons as $rr) {
            RefusalReason::firstOrCreate(['name' => $rr['name']], $rr);
        }
    }
}
