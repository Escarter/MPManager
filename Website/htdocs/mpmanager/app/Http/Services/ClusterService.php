<?php
/**
 * Created by PhpStorm.
 * User: kemal
 * Date: 2019-03-13
 * Time: 19:19
 */

namespace App\Http\Services;


use App\Models\City;
use App\Models\Cluster;
use Illuminate\Database\Eloquent\Collection;

class ClusterService
{


    /**
     * @var Cluster
     */
    private $cluster;
    /**
     * @var City
     */
    private $city;

    /**
     * ClusterService constructor.
     *
     * @param Cluster $cluster
     * @param City $city
     */
    public function __construct(Cluster $cluster, City $city)
    {
        $this->cluster = $cluster;
        $this->city = $city;
    }

    public function getClusterCities(Cluster $cluster): Collection
    {
        return $cluster->cities()->get();
    }

    public function getClusterMiniGrids(Cluster $cluster): Collection
    {
        return $cluster->miniGrids()->get();
    }


    public function getClusterList($withCities = false)
    {
        if (!$withCities) {
            return $this->cluster->get();
        }
        return $this->cluster::with('miniGrids')->get();
    }

    public function getClustersCities($clusters, $callback)
    {
        foreach ($clusters as $cluster) {
            $callback($cluster->cities()->get());
        }
    }

    public function attachCities(Cluster $cluster, $cities): void
    {
        foreach ($cities as $cityId) {
            $city = $this->city->find($cityId);
            $cluster->cities()->save($city);
        }
    }
}
