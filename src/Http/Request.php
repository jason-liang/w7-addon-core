<?php

namespace W7AddonCore\Http;

use Illuminate\Http\Request as IlluminateRequest;

class Request extends IlluminateRequest {
  public function getPathInfo()
  {
    $__path = $this->query->get('__path') ?: '/';
   
    if (! $__path) {
      throw new \Exception('we7 addon core not found __path in query params');
    }
   
    if (null === $this->pathInfo) {
        // $this->pathInfo = $this->preparePathInfo();
        $this->pathInfo = $__path;
    }

    return $this->pathInfo;
  }
}


