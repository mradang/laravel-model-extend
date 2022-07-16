<?php

namespace mradang\LaravelModelExtend\Traits;

trait ModelChangeMessageTrait
{
    private $_changeMessage = null;

    public function getChangeMessage()
    {
        return $this->_changeMessage;
    }

    protected static function bootModelChangeMessageTrait()
    {
        static::updating(function ($model) {
            if ($model->isDirty()) {
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

                $model->_changeMessage = collect($model->getDirty())
                    ->map(function ($value, $key) use ($model, $casts) {
                        $ori_value = $model->getOriginal($key);
                        if (in_array($key, $casts)) {
                            $ori_value = json_encode($ori_value, JSON_UNESCAPED_UNICODE);
                            $value = json_encode(json_decode($value, true), JSON_UNESCAPED_UNICODE);
                        }
                        return sprintf("「%s」由「%s」改为「%s」", $key, $ori_value, $value);
                    })
                    ->join(', ');
            } else {
                $model->_changeMessage = null;
            }
        });
    }
}
