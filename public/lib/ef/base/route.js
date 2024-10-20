class Route {
  constructor() {
    // Check if routes are cached in localforage
    this.routes = null;
    this.ready = this.getCachedRoutes();
  }

  async getCachedRoutes() {
    try {
      this.routes = await Common.getCache("routes");
      if (!this.routes) {
        // Fetch routes from Symfony server and cache them
        await this.fetchRoutes();
      }
    } catch (error) {
      console.error("Error fetching cached routes:", error);
    }
  }

  async fetchRoutes() {
    try {
      // Fetch routes from Symfony server
      const response = await fetch("/_symfony_routes");
      if (!response.ok) {
        throw new Error("Failed to fetch routes");
      }
      let routes = await response.json();

      // Cache routes using Common class
      if (routes && routes.data) {
        await Common.setCache("routes", routes.data);
      } else {
        console.error("Routes data is missing.");
      }

      this.routes = routes.data;
    } catch (error) {
      console.error("Error fetching routes:", error);
    }
  }

  async generate(routeName, parameters = {}) {
    await this.ready;
    
    if (!this.routes) {
      console.error("Routes not available. Please try again later.");
      return null;
    }

    const route = this.routes[routeName];
    if (!route) {
      console.warn(`Route "${routeName}" not found. Attempting to refresh routes.`);
      await this.fetchRoutes();
      route = this.routes[routeName];
      if (!route) {
        console.error(`Route "${routeName}" not found after refresh.`);
        return null;
      }
      return null;
    }

    let path = route.path;
    // Replace route parameters in path
    for (const [key, value] of Object.entries(parameters)) {
      path = path.replace(`{${key}}`, value);
    }

    // Default to GET method if methods array is empty
    const methods = route.methods.length > 0 ? route.methods : ["GET"];

    return { path, methods };
  }
}

window.Route = Route;
