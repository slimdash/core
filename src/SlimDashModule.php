<?php
namespace SlimDash\Core;

abstract class SlimDashModule
{
    /**
     * 1 - Initiate module dependencies
     * @param  SlimDashApp $app
     * @return void
     */
    abstract public function initDependencies(\SlimDash\Core\SlimDashApp $app);

    /**
     * 2 - Initiate module middlewares (route middleware should go in initRoutes)
     * @param  SlimDashApp $app
     * @return void
     */
    abstract public function initMiddlewares(\SlimDash\Core\SlimDashApp $app);

    /**
     * At this point, you have dependencies and middlewares from all modules
     * 3 - Initiate routes and route middlewares here for Slim LIFE cycle
     * Example: Auth middleware init here, then roles check can
     * init in the function above (initMiddlewares)
     * @param  SlimDashApp $app
     * @return void
     */
    abstract public function initRoutes(\SlimDash\Core\SlimDashApp $app);

    /**
     * get the module settings
     * @return string
     */
    public function getSettings()
    {
        return [];
    }

    /**
     * get the module priority
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * get the module name
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }
}
