<?php

namespace phpws2\Http;

/**
 * HttpController Abstract Class.  We highly recommend extending this for each
 * controller that can be returned by your Module instance.  It makes RESTful
 * APIs easy and fun!
 *
 * All methods are implemented by default to return 405 Method Not Allowed.
 * Override only the methods that you require within your software.
 *
 * Additionally, onBeforeExecute() and onAfterExecute() can optionally be
 * overridden within your module to do things at execute time regardless of HTTP
 * request method.
 *
 * @package phpws2
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class Controller implements \Canopy\Controller {

    private $module;

    public function __construct(\Canopy\Module $module)
    {
        $this->module = $module;
    }

    protected function getModule()
    {
        return $this->module;
    }

    public function execute(\Canopy\Request $request)
    {
        $this->onBeforeExecute($request);

        switch ($request->getMethod()) {
            case \Canopy\Request::GET:
                $response = $this->get($request);
                break;
            case \Canopy\Request::HEAD:
                $response = $this->head($request);
                break;
            case \Canopy\Request::POST:
                $response = $this->post($request);
                break;
            case \Canopy\Request::PUT:
                $response = $this->put($request);
                break;
            case \Canopy\Request::DELETE:
                $response = $this->delete($request);
                break;
            case \Canopy\Request::OPTIONS:
                $response = $this->options($request);
                break;
            case \Canopy\Request::PATCH:
                $response = $this->patch($request);
                break;
            default:
                $response = new MethodNotAllowedResponse($request);
                break;
        }


        if (!is_a($response, '\Canopy\Response')) {
            throw new \Exception(sprintf("Controller %s did not return a response object for the %s method",
                    get_class($this), $request->getMethod()));
        }

        $this->onAfterExecute($request, $response);

        return $response;
    }

    public function onBeforeExecute(\Canopy\Request &$request)
    {

    }

    public function onAfterExecute(\Canopy\Request $request, \Canopy\Response &$response)
    {

    }

    protected function getHtmlView($data, \Canopy\Request $request)
    {
        throw new \phpws2\Http\MethodNotAllowedException($request);
    }

    public function get(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function head(\Canopy\Request $request)
    {

    }

    public function post(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function put(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function delete(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function options(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function patch(\Canopy\Request $request)
    {
        return new MethodNotAllowedResponse($request);
    }

    public function getView($data, \Canopy\Request $request = null)
    {
        if (is_null($request)) {
            $request = \Canopy\Server::getCurrentRequest();
        }

        $iter = $request->getAccept()->getIterator();
        $view = null;
        foreach ($iter as $type) {
            if ($type->matches('application/json')) {
                $view = $this->getJsonView($data, $request);
                break;
            }
            if ($type->matches('application/xml')) {
                $view = $this->getXmlView($data, $request);
                break;
            }
            if ($type->matches('text/html')) {
                $view = $this->getHtmlView($data, $request);
                break;
            }
        }
        if (is_null($view)) {
            throw new NotAcceptableException($request);
        }

        return $view;
    }

    protected function getJsonView($data, \Canopy\Request $request)
    {
        return new \phpws2\View\JsonView($data);
    }

    protected function getXmlView($data, \Canopy\Request $request)
    {
        // TODO: Find a nice way to just XML encode anything and provide a
        // default view here.
        return null;
    }

}
