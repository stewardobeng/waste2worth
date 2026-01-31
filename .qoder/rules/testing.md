---
description: Testing guidelines and quality assurance practices
globs: ["**/*.test.*", "**/*.spec.*", "**/tests/**", "**/test/**"]
alwaysApply: false
---

# Testing & Quality Assurance

When writing tests and ensuring code quality:

## Testing Strategy
- Follow the testing criteria outlined in status.md
- Write unit tests for business logic and utilities
- Implement integration tests for API endpoints
- Add end-to-end tests for critical user workflows

## Test Organization
- Organize tests in a logical directory structure
- Use descriptive test names that explain the scenario
- Mock external dependencies and services
- Maintain test data and fixtures properly

## Quality Gates
- Ensure all tests pass before merging code
- Maintain adequate code coverage (aim for 80%+)
- Run linting and code formatting tools
- Test edge cases and error scenarios

@status.md
@flow.md
