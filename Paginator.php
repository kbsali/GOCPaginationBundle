<?php

namespace GOC\PaginationBundle;

use Symfony\Component\DependencyInjection\Container;
use DoctrineExtensions\Paginate\Paginate;

class Paginator implements Pagination
{
    private $container;
    private $items = 0;
    private $page = 1;
    private $pages = 1;
    private $itemsPerPage = 50;
    private $url;

    public function __construct($container, $query, $itemsPerPage, $page)
    {
        $this->container = $container;
        
        $this->setPage($page);
        $this->setItemsPerPage($itemsPerPage);

        $this->setItems( Paginate::getTotalQueryResults($query) );

        $query->setFirstResult($this->getPage() * $this->getItemsPerPage());
        $query->setMaxResults($this->getItemsPerPage());
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPages()
    {
        return ceil($this->items / $this->getItemsPerPage());
    }

    public function setItemsPerPage($items)
    {
        $this->itemsPerPage = $items;
    }

    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        if ($this->url !== null) {
            return $this->url;
        }

        $router = $this->container->get('router');
        $route  = $this->container->get('request')->attributes->get('_route');
        $params = $this->container->get('request')->attributes->all();
        $params = $params + $this->container->get('request')->query->all();
        $params['page'] = '__page__';

        foreach ($params as $key => $value) {
            if ($key{0} == '_') {
                unset($params[$key]);
            }
        }

        return $router->generate($route, $params);
    }

    public function render(array $params = array())
    {
        $page = $this->getPage()+1;
        $pages = ceil($this->getPages());
        $result = array();

        if ($page > 1 && $pages > 1) {
            $result[] = '<li><a href="' . $this->generateUrl($page-1) . '" class="prev">«</a></li>';
        }

        if ($page > 4) {
            $result[] = '<li><a href="' . $this->generateUrl(1) . '" class="first">1</a></li>';
            if ($page-1 > 4) {
                $result[] = '<li>...</li>';
            }
        }

        for ($i = $page-3; $i < $page+4; $i++) {
            if ($i > 0 && $i <= $pages) {
                $result[] = '<li><a href="' . $this->generateUrl($i) . '" class="' . (($i != $page) ?: 'active') . '">' . $i . '</a></li>';
            }
        }

        if ($page+3 < $pages) {
            if ($page+4 < $pages) {
                $result[] = '<li>...</li>';
            }
            $result[] = '<li><a href="' . $this->generateUrl($pages) . '" class="last">' . $pages . '</a></li>';
        }

        if ($page < $pages && $pages > 1) {
            $result[] = '<li><a href="' . $this->generateUrl($page+1) . '" class="next">»</a></li>';
        }

        return '<div class="Pagination"><ol class="Pagination">' . implode(PHP_EOL, $result) . '</ol></div>';
    }

    protected function generateUrl($page)
    {
        return str_replace('__page__', $page, $this->getUrl());
    }
}