---
description: Database design and API integration guidelines
globs: ["**/models/**", "**/migrations/**", "**/database/**", "**/api/**", "**/*.sql"]
alwaysApply: false
---

# Database & API Guidelines

When working with data persistence and external APIs:

## Database Design
- Follow the data model specifications in database.md
- Follow the schema design and relationships defined in database.md
- Implement proper relationships and constraints as documented
- Use appropriate data types and indexing strategies from database.md
- Plan for data migration and versioning

## API Integration
- Reference api-docs.md for complete API endpoint specifications
- Follow the authentication methods defined in api-docs.md
- Implement error handling for external API calls
- Use proper timeout and retry strategies
- Cache API responses when appropriate
- Handle rate limiting gracefully

## Data Security
- Implement proper data encryption for sensitive information
- Follow GDPR/privacy compliance requirements if applicable
- Use parameterized queries to prevent SQL injection
- Implement proper backup and recovery procedures

@database.md
@api-docs.md
@backend.md
@flow.md
