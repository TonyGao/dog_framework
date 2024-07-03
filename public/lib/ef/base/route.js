class Route {
  constructor() {
    // Check if routes are cached in localforage
    this.routes = null;
    this.getCachedRoutes();
  }

  async getCachedRoutes() {
    try {
      this.routes = await Common.getCache("routes");
      if (!this.routes) {
        // Fetch routes from Symfony server and cache them
        await this.fetchRoutes();
      }
      // Now that routes are available, proceed with generating routes
      // Move this line here from the constructor
      const submitFieldsRoute = this.generate("api_platform_entity_submitFields");
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
      const routes = await response.json();

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

  generate(routeName, parameters = {}) {
    if (!this.routes) {
      console.error("Routes not available. Please try again later.");
      return null;
    }

    const route = this.routes[routeName];
    if (!route) {
      console.error(`Route "${routeName}" not found.`);
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
