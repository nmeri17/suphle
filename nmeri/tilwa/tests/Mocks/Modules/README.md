ModuleOne is the sandbox used for testing a majority of functionality, with the exception of those requiring configuration different from the predominant default

Tests requiring authentication or events need a fresh module since its evaluation (and possible failure of authentication) is handled at the fringes of the framework rather than by an individual component