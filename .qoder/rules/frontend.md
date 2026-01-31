---
description: Frontend development guidelines and best practices
globs: ["**/*.tsx", "**/*.jsx", "**/*.css", "**/*.scss", "**/*.js", "**/*.ts", "**/components/**", "**/pages/**", "**/styles/**"]
alwaysApply: false
---

# Frontend Development Rules

When working on frontend components and pages:

## Component Development
- Create reusable, modular components following the component structure outlined in frontend.md
- Use TypeScript for type safety and better development experience
- Implement proper state management as specified in the documentation
- Follow the UI/UX guidelines provided in the project documentation

## Styling Guidelines
- Use the CSS framework/approach specified in techstack.md
- Maintain consistent styling patterns throughout the application
- Implement responsive design for mobile and desktop viewports
- Follow accessibility best practices (ARIA labels, semantic HTML)

## Performance Best Practices
- Optimize bundle size and implement code splitting where appropriate
- Use lazy loading for routes and heavy components
- Optimize images and assets
- Implement proper caching strategies

## Testing
- Write unit tests for complex components and utilities
- Test user interactions and edge cases
- Ensure cross-browser compatibility

@frontend.md
@techstack.md
@prd.md
