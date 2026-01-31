---
description: Backend development guidelines and API implementation
globs: ["**/*.py", "**/*.js", "**/*.ts", "**/api/**", "**/server/**", "**/models/**", "**/routes/**", "**/controllers/**"]
alwaysApply: false
---

# Backend Development Rules

When implementing backend functionality:

## API Development
- Follow the API design patterns outlined in backend.md and api-docs.md
- Implement proper HTTP status codes and error responses
- Use consistent naming conventions for endpoints
- Document API endpoints with clear request/response examples as shown in api-docs.md

## Data Management
- Implement the data models as specified in backend.md and database.md
- Follow the database schema design in database.md
- Use proper database indexing and query optimization
- Implement data validation and sanitization
- Handle database migrations properly

## Security Implementation
- Follow the security guidelines outlined in the backend documentation
- Implement proper authentication and authorization
- Validate and sanitize all user inputs
- Use environment variables for sensitive configuration

## Performance & Scalability
- Implement caching strategies where appropriate
- Use async/await patterns for non-blocking operations
- Monitor and log application performance
- Design for horizontal scaling if specified

@backend.md
@api-docs.md
@database.md
@techstack.md
@flow.md
