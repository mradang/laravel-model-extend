<?php

namespace mradang\LaravelModelExtend\Traits;

use Illuminate\Support\Collection;

trait ModelChangeMessageTrait
{
    private Collection $_changes;

    public function getModelChanges($keys = null): Collection
    {
        if (is_string($keys)) {
            return $this->_changes->only([$keys]);
        } elseif (is_array($keys)) {
            return $this->_changes->only($keys);
        } else {
            return $this->_changes;
        }
    }

    public function getChangeMessage($keys = null): string
    {
        return $this->getModelChanges($keys)
            ->map(function ($item, $key) {
                return sprintf('「%s」由「%s」改为「%s」', $key, $item['old_value'], $item['new_value']);
            })
            ->join('，');
    }

    protected static function bootModelChangeMessageTrait()
    {
        static::saving(function ($model) {
            // 获取数组转换字段名
            $casts = collect($model->getCasts())
                ->map(function ($value, $key) {
                    return $value === 'array' ? $key : '';
                })
                ->filter(function ($value) {
                    return !empty($value);
                })
                ->values()
                ->all();

            // 记录变更的数据
            if ($model->isDirty()) {
                $model->_changes = collect($model->getDirty())
                    ->map(function ($value, $key) use ($model, $casts) {
                        $old_value = in_array($key, $casts)
                            ? json_encode($model->getOriginal($key), JSON_UNESCAPED_UNICODE)
                            : $model->getOriginal($key);

                        $new_value = in_array($key, $casts)
                            ? json_encode(json_decode($value, true), JSON_UNESCAPED_UNICODE)
                            : $value;

                        return compact('old_value', 'new_value');
                    });
            } else {
                $model->_changes = collect([]);
            }
        });
    }
}
