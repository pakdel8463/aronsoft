<?php

namespace App\Helpers;

class DynamicRouteHelper
{
    public static function resolveRouteKey($model)
    {
        $fields = config('dynamic-routes.fields');

        foreach ($fields as $field) {
            if (isset($model->$field) && !empty($model->$field)) {
                return $model->$field;
            }
        }

        // اگر هیچ فیلدی موجود نبود، حداقل id را برگردان
        return $model->id;
    }
}
