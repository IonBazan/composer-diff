# URL Generators

URL generators are used to generate URLs for the packages listed in a diff:

- `GitHubGenerator`: Generates URLs for GitHub repositories.
- `GitLabGenerator`: Generates URLs for GitLab repositories. Supports custom domains.
- `BitbucketGenerator`: Generates URLs for Bitbucket repositories.
- `DrupalGenerator`: Generates URLs for Drupal packages.

They are chosen automatically based on the package URL or other conditions specified in `supportsPackage()` method.

Each generator must have following methods:

- `supportsPackage()`: Checks if the generator supports the package.
- `getCompareUrl()`: Generates URL for comparing two versions of the package.
- `getReleaseUrl()`: Generates URL for viewing a release or commit of a package.
- `getProjectUrl()`: Mainly used to generate URL to the project repository root.
