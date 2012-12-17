# Fuu Framework
#### A simple PHP 5.3 framework

Notice that this not production-ready and should not be used in production because not 
all components are tested and optimized. Feel free to use but use it with your own risk.

Bootstrap:
~~~
use Fuu\Mvc\Application;

chdir(dirname(__DIR__));
include_once '/library/Fuu/Mvc/Application.php';

$app = new Application(include '/apps/HelloWorld/config/Application.php');
echo $app->dispatch();
~~~

Simple configuration file example:
~~~
return array(
    'app_namespace' => 'HelloWorld',
    'environment'   => 'development',
    'public_path'   => '/path/to/public_html',
    'app_path'      => '/path/to/app',
    'autoload'      => array(
        'namespaces'    => array(
            'Doctrine'      => '/cloned_repos/php/DoctrineORM/lib/Doctrine'
            'Everzet'       => '/cloned_repos/php/jade.php/src/Everzet'
        ),
        'prefixes'      => array(
            'Mustache'      => '/cloned_repos/php/mustache.php/src/Mustache',
            'Twig'          => '/cloned_repos/php/Twig/lib/Twig'
        )
    )
);
~~~

The controller:
~~~
namespace HelloWorld\Modules\Index;
use Fuu\Mvc\Action\Controller;

class Index extends Controller
{
    /**
     * @Action // All controller actions should annotated with @Action tag
     */
    public function index($request, $response)
    {
        return 'Hello World';
    }
    
    /**
     * @Action
     * @Render(renderer="Twig") // Using Twig template engine
     */
    public function test_twig($request, $response)
    {
        $greeting = 'Hello World';
        return $this->render('{{ greeting }}', compact('greeting'), 'string');
    }
    
    /**
     * @Action(accepts={"html"}) // Only accepts .html request
     * @Render(renderer="Jade") // Using Jade template engine
     */
    public function test_jade($request, $response)
    {
        $greeting = 'Hello World';
        
        $jade = "
        !!! 5
        html
        head
            title Hello World
        body
            p
                | {:greeting}
                | I'm Fuu
        ";
        
        return $this->render($jade, compact('greeting'), 'string');
    }
    
    /**
     * @Action(accepts={"json"}) // Only accepts .json request
     * @Response(type="application/json", charset="utf8") // Output response as a json document
     * @Cache(key="test1", adapter="Apc", config={expiry="+1 hour"}) // Cache the output for 1 hour
     */
    public function test_json($request, $response)
    {
        $greeting = 'Hello World';
        return json_encode(conpact('greeting'));
    }
    
    /**
     * @Action // If 'render' method is not explicitly called, the controller will find the view file inside 'views' dir
     * @Cache(key="test2", adapter="PhpFile", strategies={"Serializer"}) // Use PHP file to cache an array
     */
    public function test_cached_array($request, $response)
    {
        $greeting = 'Hello World';
        return compact('greeting');
    }
    
    /**
     * @Action
     * @Redirect(uri="error/404", ifnot="mobile", order="ifnot-if") // Force user to access the page using mobile browser
     */
    public function test_mobile_browser($request, $response)
    {
        return 'Hello World';
    }
}
~~~
