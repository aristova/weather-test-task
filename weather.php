<?php

/**
 * Функция для определения самого холодного дня из ближайших пяти.
 * Используется  API openweathermap.org
 * Возвращается JSON, содержащий название города, его id и координаты, а также
 * информацию о самом холодном дне.
 *
 * @param int $location
 *    Id of the city
 * @param string $keyApi
 *    Api key for account
 * @return string or void
 *    JSON object
 */

function getWeatherInfo(int $location, string $keyApi)
{
    if (!ini_get('allow_url_fopen')) {
        echo "Обращение к URL не разрешено в настройках";
        return;
    }

    $json = file_get_contents("http://api.openweathermap.org/data/2.5/forecast?id=$location&appid=$keyApi");
    if (!$json) {
        echo "Невозможно получить данные. Возможно, передан некорректный ключ или расположение";
        return;
    }

    $dataWeather = json_decode($json);
    if ($dataWeather->cod == 404) {
        echo($dataWeather->message);
        return;
    }

    // Массив для сохранения данных для JSON объекта.
    $weather = [];

    // Создаем массив, где ключами будут дни, а значениями - температура.
    $daysTemp = [];
    foreach ($dataWeather->list as $key => $value) {
        $daysTemp[$value->dt] = $value->main->temp_min;
    }

    $coldestDay = array_keys($daysTemp, min($daysTemp));

    foreach ($dataWeather->list as $key => $value) {
        // Находим остальные данные для дня с минимальной температурой.
        if ($value->dt === $coldestDay[0]) {
            // Переводим Кельвины в Цельсия и округляем.
            $tempMinDegreeCelsius = round($value->main->temp_min - 273);
            // Формируем массив данных, чтобы потом отдать его как JSON объект.
            $weather = [
              "city" => [
                "id" => $dataWeather->city->id,
                "name" => $dataWeather->city->name,
                "coord" => [
                  "lon" => $dataWeather->city->coord->lon,
                  "lat" => $dataWeather->city->coord->lat,
                ],
              ],
              "coldest_day" => [
                "date" => date("Y-m-d", $value->dt),
                "min_temperature" => $tempMinDegreeCelsius,
                "max_wind" => $value->wind->speed,
              ],
            ];
        }
    }
    return json_encode($weather);
}

// Отображаем JSON (для Москвы)
echo getWeatherInfo(524901, 'fd813d2a4171bf3081ab828d2507cf82');
