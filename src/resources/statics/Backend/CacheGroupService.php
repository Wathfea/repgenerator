<?php

namespace App\Abstraction\Cache;

use Illuminate\Support\Facades\Cache;

final class CacheGroupService
{
    /**
     * @param string $groupName
     * @param string $key
     * @return bool
     */
    public static function addCache(string $groupName, string $key): bool {
        $cacheGroup = new CacheGroup();
        $cacheGroup->setAttribute(CacheGroup::NAME_COLUMN, $groupName);
        $cacheGroup->setAttribute(CacheGroup::KEY_COLUMN, $key);
        return $cacheGroup->save();
    }

    /**
     * @param string $groupName
     * @return bool
     */
    public static function invalidateGroup(string $groupName): bool {
        /** @var CacheGroup[] $caches */
        $caches = CacheGroup::where(CacheGroup::NAME_COLUMN, $groupName)->get();
        foreach ( $caches as $cache ) {
            Cache::forget($cache->getAttribute(CacheGroup::KEY_COLUMN));
        }
        return true;
    }
}
