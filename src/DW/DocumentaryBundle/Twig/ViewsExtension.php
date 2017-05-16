<?php

namespace DW\DocumentaryBundle\Twig;

class ViewsExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('views', [$this, 'views']),
        ];
    }

    /**
     * @param int $views
     * @return string
     */
    public function views(int $views)
    {
        $views = abs($views);

        $suffix = 'K,M,B';
        $suffix = explode(',', $suffix);

        if ($views < 1000) { // any number less than a Thousand
            $shorted = number_format($views);
        } elseif ($views < 1000000) { // any number less than a million
            $shorted = number_format($views/1000, 2).$suffix[0];
        } elseif ($views < 1000000000) { // any number less than a billion
            $shorted = number_format($views/1000000, 2).$suffix[1];
        } else { // at least a billion
            $shorted = number_format($views/1000000000, 2).$suffix[2];
        }

        return $shorted;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'views';
    }
}