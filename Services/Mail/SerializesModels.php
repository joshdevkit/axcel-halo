<?php

namespace Axcel\AxcelCore\Services\Mail;

use ReflectionClass;

trait SerializesModels
{
    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = (new ReflectionClass($this))->getProperties();
        $propertyNames = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $propertyValue = $property->getValue($this);

            if ($this->isModel($propertyValue)) {
                $this->$propertyName = $this->serializeModel($propertyValue);
            }

            $propertyNames[] = $propertyName;
        }

        return $propertyNames;
    }

    /**
     * Restore the instance after serialization.
     */
    public function __wakeup()
    {
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();
            $propertyValue = $property->getValue($this);

            if ($this->isSerializedModel($propertyValue)) {
                $this->$propertyName = $this->unserializeModel($propertyValue);
            }
        }
    }

    /**
     * Check if the given value is a model.
     *
     * @param mixed $model
     * @return bool
     */
    protected function isModel($model): bool
    {
        return is_object($model) && method_exists($model, 'find') && property_exists($model, 'id');
    }

    /**
     * Serialize a model instance into an array.
     *
     * @param object $model
     * @return array
     */
    protected function serializeModel($model): array
    {
        return [
            'class' => get_class($model),
            'id' => $model->id,
        ];
    }

    /**
     * Check if the given value is a serialized model.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isSerializedModel($value): bool
    {
        return is_array($value) && isset($value['class'], $value['id']);
    }

    /**
     * Unserialize a model instance from its class and ID.
     *
     * @param array $data
     * @return object|null
     */
    protected function unserializeModel(array $data)
    {
        $class = $data['class'];
        $id = $data['id'];

        if (!class_exists($class)) {
            return null;
        }

        // Ensure the model class has a static find() method
        if (!method_exists($class, 'find')) {
            return null;
        }

        return $class::find($id);
    }
}
