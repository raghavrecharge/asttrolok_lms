# Stitch MCP Server Configuration

This project includes a Model Context Protocol (MCP) server for Stitch integration.

## Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Configure Environment Variables

Add your Stitch API key to your `.env` file:

```env
STITCH_API_KEY=your_stitch_api_key_here
STITCH_API_URL=https://api.stitch.com
```

### 3. Run the MCP Server

```bash
npm run mcp:stitch
```

## Available Tools

The Stitch MCP server provides the following tools:

### Project Management
- `stitch_create_project` - Create a new Stitch project
- `stitch_list_projects` - List all Stitch projects
- `stitch_get_project` - Get details of a specific project
- `stitch_update_project` - Update a Stitch project
- `stitch_delete_project` - Delete a Stitch project

### Deployment Management
- `stitch_deploy_project` - Deploy a Stitch project
- `stitch_get_deployment_status` - Get deployment status
- `stitch_get_logs` - Get deployment logs

### Environment Management
- `stitch_list_environments` - List project environments
- `stitch_create_environment` - Create a new environment

## MCP Configuration

Add this to your MCP client configuration:

```json
{
  "mcpServers": {
    "stitch": {
      "command": "node",
      "args": ["stitch-mcp-server.js"],
      "env": {
        "STITCH_API_KEY": "your_api_key_here",
        "STITCH_API_URL": "https://api.stitch.com"
      }
    }
  }
}
```

## Usage Examples

### Create a new project
```javascript
// Using the MCP tool
await mcp.callTool('stitch_create_project', {
  name: 'my-new-project',
  description: 'A new web application',
  template: 'web-app'
});
```

### List projects
```javascript
const projects = await mcp.callTool('stitch_list_projects', {
  limit: 20,
  offset: 0
});
```

### Deploy a project
```javascript
await mcp.callTool('stitch_deploy_project', {
  projectId: 'project-123',
  environment: 'production'
});
```

## Security Notes

- Keep your Stitch API key secure and never commit it to version control
- Use environment variables for sensitive configuration
- Regularly rotate your API keys
- Monitor API usage and access logs

## Troubleshooting

### Common Issues

1. **API Key Not Found**: Ensure `STITCH_API_KEY` is set in your environment
2. **Connection Errors**: Check your internet connection and API URL
3. **Permission Denied**: Verify your API key has the required permissions

### Debug Mode

Set `NODE_ENV=development` to enable debug logging:

```bash
NODE_ENV=development npm run mcp:stitch
```

## Support

For issues with the MCP server:
1. Check the logs for error messages
2. Verify your API configuration
3. Test your Stitch API key directly

For Stitch platform issues:
- Consult the Stitch documentation
- Contact Stitch support
- Check the Stitch status page
