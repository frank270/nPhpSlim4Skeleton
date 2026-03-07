# Locations Module Completion Plan

## Goal
Complete the "Locations" module by implementing the frontend API (including search and filtering) and adding PHPUnit tests to ensure reliability.

## User Review Required
> [!IMPORTANT]
> This plan involves installing `phpunit/phpunit` as a development dependency.

## Proposed Changes

### Dependencies
- (Skipped by user request) `composer require --dev phpunit/phpunit`

### Backend
#### [NEW] [LocationAction.php](file:///Users/n/Projects/1fBreakFast/www/app/Actions/Front/LocationAction.php)
- Create a new Action class for frontend logic.
- Implement `apiList` with support for `county` and `keyword` filters.
- Implement `apiGetCounties` to return available counties.
- Implement `apiGetOne` for single store details.
- **Note**: Will reuse `App\Models\LocationStoresModel` as it already supports filtering.

#### [NEW] [front_locations.php](file:///Users/n/Projects/1fBreakFast/www/app/Routes/front_locations.php)
- Define routes:
    - `GET /api/front/locations` -> `LocationAction::apiList`
    - `GET /api/front/locations/cities` -> `LocationAction::apiGetCounties`
    - `GET /api/front/locations/{id}` -> `LocationAction::apiGetOne`

#### [MODIFY] [routes.php](file:///Users/n/Projects/1fBreakFast/www/app/routes.php)
- Register the new `front_locations.php` route file.

### Tests
#### [NEW] [phpunit.xml](file:///Users/n/Projects/1fBreakFast/www/phpunit.xml)
- Configure PHPUnit for the project.

#### [NEW] [LocationStoresModelTest.php](file:///Users/n/Projects/1fBreakFast/www/tests/Unit/LocationStoresModelTest.php)
- Create unit tests for `LocationStoresModel`.
- Test `paginate` with various filters (county, keyword).
- Test `getCounties`.

## Verification Plan

### Automated Tests
- Run `vendor/bin/phpunit` to execute the new unit tests.

### Manual Verification
- Use `curl` to test the new API endpoints:
    - List all: `curl "http://localhost:8080/api/front/locations"`
    - Filter by county: `curl "http://localhost:8080/api/front/locations?county=Taipei"`
    - Search: `curl "http://localhost:8080/api/front/locations?keyword=StoreName"`
    - Get one: `curl "http://localhost:8080/api/front/locations/1"`
