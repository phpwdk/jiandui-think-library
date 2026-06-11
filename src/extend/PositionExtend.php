<?php
/**
 * 代码维护 ( 汪登科 )
 * 联系微信 ( wk9653992 )
 * 启动日期 ( 2022/4/13 15:45 )
 */

declare (strict_types=1);

namespace think\simple\extend;

/**
 * 位置处理扩展
 * Class PositionExtend
 *
 * @package think\simple\extend
 */
class PositionExtend
{
    static int $earth_radius = 6371393; // 地球半径

    /**
     * 坐标转位置
     *
     * @param string $location
     *
     * @return array
     */
    public static function geocoder(string $location): array
    {
        $url    = 'https://apis.map.qq.com/ws/geocoder/v1/?key=' . APIS_MAP_QQ_KEY . '&location=' . $location;
        $result = http_get($url);
        return json_decode_array($result);
    }

    /**
     * IP转位置
     *
     * @param string $ip
     *
     * @return array
     */
    public static function ip2location(string $ip): array
    {
        $url    = 'https://apis.map.qq.com/ws/location/v1/ip?ip=' . $ip . '&key=' . APIS_MAP_QQ_KEY;
        $result = http_get($url);
        return json_decode_array($result);
    }

    /**
     * 经纬度 两点间的距离
     *
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     *
     * @return float
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $radius    = self::$earth_radius;
        $dlat = deg2rad($lat2 - $lat1);
        $dlng = deg2rad($lng2 - $lng1);
        $a    = pow(sin($dlat / 2), 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * pow(sin($dlng / 2), 2);
        $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d    = $radius * $c;
        return round($d);
    }

    /**
     * 返回范围内的坐标
     *
     * @param float $lat
     * @param float $lng
     * @param int   $distance 单位米
     *
     * @return array
     */
    public static function getDistanceSquares(float $lat, float $lng, int $distance = 5000): array
    {
        $radius    = static::$earth_radius;
        $dlng = 2 * asin(sin($distance / (2 * $radius)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance / $radius;
        $dlat = rad2deg($dlat);
        return [
            'left-top'     => ['lat' => $lat + $dlat, 'lng' => $lng - $dlng],
            'right-top'    => ['lat' => $lat + $dlat, 'lng' => $lng + $dlng],
            'left-bottom'  => ['lat' => $lat - $dlat, 'lng' => $lng - $dlng],
            'right-bottom' => ['lat' => $lat - $dlat, 'lng' => $lng + $dlng],
        ];
    }

    /**
     * 根据坐标返回距离
     *
     * @param array $data
     * @param array $location
     *
     * @return array
     */
    public static function srotDistance(array &$data, array $location): array
    {
        return array_map(function ($item) use ($location) {
            $distance         = static::getDistance($item['lat'], $item['lng'], $location['lat'], $location['lng']);
            $item['distance'] = intval($distance);
            return $item;
        }, $data);
    }
}
