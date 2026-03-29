# API Usage Guide

## REST API

### Authentication

The REST API uses session-based authentication. Log in first:

```json
POST https://yourdomain.com/api/v1/auth/login
Content-Type: application/json

{
    "username": "admin",
    "password": "your-password"
}
```

### Endpoints

```text
Content:
- GET    /api/v1/content              List content
- GET    /api/v1/content/{id}         Get content by ID
- POST   /api/v1/content              Create content
- PUT    /api/v1/content/{id}         Update content
- DELETE /api/v1/content/{id}         Delete content

Users:
- GET    /api/v1/users                List users (admin only)
- GET    /api/v1/users/{id}           Get user by ID
- GET    /api/v1/users/me             Get current user
- POST   /api/v1/users                Create user (admin only)
- PUT    /api/v1/users/{id}           Update user
- DELETE /api/v1/users/{id}           Delete user (admin only)

Search:
- GET    /api/v1/search               Search content

Blocks:
- GET    /api/v1/blocks               List blocks
- GET    /api/v1/blocks/regions       List regions
- POST   /api/v1/blocks/assign        Assign block to region

Media:
- POST   /api/v1/media/upload         Upload file
- GET    /api/v1/media/{id}           Get media details
- DELETE /api/v1/media/{id}           Delete media
```

### Examples

List Content:
`GET /api/v1/content?type=article&limit=10&page=1`

Response:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "My First Article",
            "slug": "my-first-article",
            "status": "published",
            "created_at": "2024-01-01T12:00:00+00:00"
        }
    ],
    "meta": {
        "pagination": {
            "page": 1,
            "limit": 10,
            "total": 25,
            "pages": 3
        }
    }
}
```

Create Content:
```json
POST /api/v1/content
Content-Type: application/json

{
    "content_type": "article",
    "title": "New Article",
    "body": "Article content here...",
    "language": "en",
    "status": "draft"
}
```

Search:
`GET /api/v1/search?q=hello+world&type=content&limit=20`

## GraphQL API

Endpoint:
`POST https://yourdomain.com/graphql`

Playground:
`GET https://yourdomain.com/graphql/playground`

Example Query:
```json
query GetContent($id: Int!) {
    content(id: $id) {
        id
        title
        body
        status
        author {
            username
            email
        }
        created_at
    }
}
```

Variables:
```json
{
    "id": 123
}
```

Example Mutation:
```json
mutation CreateArticle($input: ContentInput!) {
    createContent(input: $input) {
        id
        title
        status
    }
}
```

Variables:
```json
{
    "input": {
        "content_type": "article",
        "title": "My Article",
        "body": "Content here...",
        "language": "en"
    }
}
```

## Rate Limiting

API requests are limited to 100 requests per minute per IP address.

## Error Handling

All errors follow this format:
```json
{
    "success": false,
    "error": {
        "message": "Error description",
        "code": 400,
        "details": {
            "field": "Specific error details"
        }
    }
}
```

## HTTP Status Codes

```markdown
200 - Success
201 - Created
204 - No Content
400 - Bad Request
401 - Unauthorized
403 - Forbidden
404 - Not Found
422 - Validation Error
429 - Too Many Requests
500 - Internal Server Error
```