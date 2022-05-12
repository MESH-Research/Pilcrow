<?php
declare(strict_types=1);

namespace App\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CleanAdminHtml implements CastsAttributes
{
    /**
     * Wrap the clean method on purifier to set the config value.
     *
     * @param string $value
     * @return string
     */
    protected function clean($value)
    {
        return app('purifier')->clean($value, 'admin_fields');
    }

    /**
     * Clean the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $_
     * @return mixed
     */
    public function get($model, $key, $value, $_)
    {
        return $this->clean($value);
    }

    /**
     * Clean the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $_
     * @return mixed
     */
    public function set($model, $key, $value, $_)
    {
        return $this->clean($value);
    }
}
