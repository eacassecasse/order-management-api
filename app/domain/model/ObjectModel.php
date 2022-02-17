<?php

namespace app\domain\model;

use App\domain\model\GenericModel;

class ObjectModel implements GenericModel
{

    public function __equals($object): ?bool
    {

        $className = static::class;

        if ($object == null) {
            return false;
        }

        if ($this == $object) {
            return true;
        }

        if (!$object instanceof $className) {
            return false;
        }

        $methods = get_class_methods($className);
        $workable_methods = $this->getWorkableMethods($methods);
        $count_methods = count($workable_methods);

        return eval($this->getEvaluableString($workable_methods, $count_methods));
    }

    private function getWorkableMethods(?array $methods)
    {

        $workable_methods = array();

        foreach ($methods as $method) {
            if (substr($method, 0, 3) == 'get' && substr($method, -1) != 's') {
                if ($method != 'getId') {
                    array_push($workable_methods, $method);
                }
            }
        }

        return $workable_methods;
    }

    private function getEvaluableString(array $workable_methods, int $methods_length): ?string
    {

        for ($i = 0; $i < $methods_length; $i++) {
            if ($i == 0) {
                $stringToEval = $stringToEval
                    . '$object->{' . $workable_methods[0] . '}() == $this->{'
                    . $workable_methods[0] . '}()';
            }
            else {
                $stringToEval = $stringToEval
                    . ' && $object->{' . $workable_methods[$i]
                    . '}() == $this->{'
                    . $workable_methods[$i] . '}()';
            }
        }

        return $stringToEval;
    }

}
