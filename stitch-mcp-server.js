#!/usr/bin/env node

const { Server } = require('@modelcontextprotocol/sdk/server/index.js');
const { StdioServerTransport } = require('@modelcontextprotocol/sdk/server/stdio.js');
const {
  CallToolRequestSchema,
  ErrorCode,
  ListToolsRequestSchema,
  McpError,
} = require('@modelcontextprotocol/sdk/types.js');

class StitchMCPServer {
  constructor() {
    this.server = new Server(
      {
        name: 'stitch-mcp-server',
        version: '0.1.0',
      },
      {
        capabilities: {
          tools: {},
        },
      }
    );

    this.apiKey = process.env.STITCH_API_KEY;
    this.apiUrl = process.env.STITCH_API_URL || 'https://api.stitch.com';
    
    if (!this.apiKey) {
      throw new Error('STITCH_API_KEY environment variable is required');
    }

    this.setupToolHandlers();
    this.setupErrorHandling();
  }

  setupErrorHandling() {
    this.server.onerror = (error) => console.error('[MCP Error]', error);
    process.on('SIGINT', async () => {
      await this.server.close();
      process.exit(0);
    });
  }

  setupToolHandlers() {
    this.server.setRequestHandler(ListToolsRequestSchema, async () => ({
      tools: [
        {
          name: 'stitch_create_project',
          description: 'Create a new Stitch project',
          inputSchema: {
            type: 'object',
            properties: {
              name: {
                type: 'string',
                description: 'Project name',
              },
              description: {
                type: 'string',
                description: 'Project description',
              },
              template: {
                type: 'string',
                description: 'Project template',
                enum: ['blank', 'web-app', 'mobile-app', 'api'],
                default: 'blank',
              },
            },
            required: ['name'],
          },
        },
        {
          name: 'stitch_list_projects',
          description: 'List all Stitch projects',
          inputSchema: {
            type: 'object',
            properties: {
              limit: {
                type: 'number',
                description: 'Maximum number of projects to return',
                default: 10,
              },
              offset: {
                type: 'number',
                description: 'Number of projects to skip',
                default: 0,
              },
            },
          },
        },
        {
          name: 'stitch_get_project',
          description: 'Get details of a specific Stitch project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
            },
            required: ['projectId'],
          },
        },
        {
          name: 'stitch_update_project',
          description: 'Update a Stitch project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              name: {
                type: 'string',
                description: 'Updated project name',
              },
              description: {
                type: 'string',
                description: 'Updated project description',
              },
            },
            required: ['projectId'],
          },
        },
        {
          name: 'stitch_delete_project',
          description: 'Delete a Stitch project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              confirm: {
                type: 'boolean',
                description: 'Confirm deletion',
                default: false,
              },
            },
            required: ['projectId', 'confirm'],
          },
        },
        {
          name: 'stitch_deploy_project',
          description: 'Deploy a Stitch project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              environment: {
                type: 'string',
                description: 'Deployment environment',
                enum: ['development', 'staging', 'production'],
                default: 'development',
              },
            },
            required: ['projectId'],
          },
        },
        {
          name: 'stitch_get_deployment_status',
          description: 'Get deployment status for a project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              deploymentId: {
                type: 'string',
                description: 'Deployment ID (optional, gets latest if not provided)',
              },
            },
            required: ['projectId'],
          },
        },
        {
          name: 'stitch_list_environments',
          description: 'List all environments for a project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
            },
            required: ['projectId'],
          },
        },
        {
          name: 'stitch_create_environment',
          description: 'Create a new environment for a project',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              name: {
                type: 'string',
                description: 'Environment name',
              },
              type: {
                type: 'string',
                description: 'Environment type',
                enum: ['development', 'staging', 'production'],
              },
              variables: {
                type: 'object',
                description: 'Environment variables',
                additionalProperties: {
                  type: 'string',
                },
              },
            },
            required: ['projectId', 'name', 'type'],
          },
        },
        {
          name: 'stitch_get_logs',
          description: 'Get logs for a project deployment',
          inputSchema: {
            type: 'object',
            properties: {
              projectId: {
                type: 'string',
                description: 'Project ID',
              },
              deploymentId: {
                type: 'string',
                description: 'Deployment ID (optional, gets latest if not provided)',
              },
              lines: {
                type: 'number',
                description: 'Number of log lines to retrieve',
                default: 100,
              },
              follow: {
                type: 'boolean',
                description: 'Follow log stream',
                default: false,
              },
            },
            required: ['projectId'],
          },
        },
      ],
    }));

    this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
      const { name, arguments: args } = request.params;

      try {
        switch (name) {
          case 'stitch_create_project':
            return await this.createProject(args);
          case 'stitch_list_projects':
            return await this.listProjects(args);
          case 'stitch_get_project':
            return await this.getProject(args);
          case 'stitch_update_project':
            return await this.updateProject(args);
          case 'stitch_delete_project':
            return await this.deleteProject(args);
          case 'stitch_deploy_project':
            return await this.deployProject(args);
          case 'stitch_get_deployment_status':
            return await this.getDeploymentStatus(args);
          case 'stitch_list_environments':
            return await this.listEnvironments(args);
          case 'stitch_create_environment':
            return await this.createEnvironment(args);
          case 'stitch_get_logs':
            return await this.getLogs(args);
          default:
            throw new McpError(
              ErrorCode.MethodNotFound,
              `Unknown tool: ${name}`
            );
        }
      } catch (error) {
        console.error(`Error executing tool ${name}:`, error);
        throw new McpError(
          ErrorCode.InternalError,
          `Tool execution failed: ${error.message}`
        );
      }
    });
  }

  async makeRequest(endpoint, options = {}) {
    const url = `${this.apiUrl}${endpoint}`;
    const headers = {
      'Authorization': `Bearer ${this.apiKey}`,
      'Content-Type': 'application/json',
      ...options.headers,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      return await response.json();
    } catch (error) {
      throw new Error(`Stitch API request failed: ${error.message}`);
    }
  }

  async createProject(args) {
    const project = await this.makeRequest('/projects', {
      method: 'POST',
      body: JSON.stringify({
        name: args.name,
        description: args.description,
        template: args.template || 'blank',
      }),
    });

    return {
      content: [
        {
          type: 'text',
          text: `Project created successfully:\n\n${JSON.stringify(project, null, 2)}`,
        },
      ],
    };
  }

  async listProjects(args) {
    const params = new URLSearchParams({
      limit: args.limit || 10,
      offset: args.offset || 0,
    });

    const response = await this.makeRequest(`/projects?${params}`);

    return {
      content: [
        {
          type: 'text',
          text: `Projects:\n\n${JSON.stringify(response, null, 2)}`,
        },
      ],
    };
  }

  async getProject(args) {
    const project = await this.makeRequest(`/projects/${args.projectId}`);

    return {
      content: [
        {
          type: 'text',
          text: `Project details:\n\n${JSON.stringify(project, null, 2)}`,
        },
      ],
    };
  }

  async updateProject(args) {
    const project = await this.makeRequest(`/projects/${args.projectId}`, {
      method: 'PUT',
      body: JSON.stringify({
        name: args.name,
        description: args.description,
      }),
    });

    return {
      content: [
        {
          type: 'text',
          text: `Project updated successfully:\n\n${JSON.stringify(project, null, 2)}`,
        },
      ],
    };
  }

  async deleteProject(args) {
    if (!args.confirm) {
      throw new Error('Project deletion must be confirmed');
    }

    await this.makeRequest(`/projects/${args.projectId}`, {
      method: 'DELETE',
    });

    return {
      content: [
        {
          type: 'text',
          text: `Project ${args.projectId} deleted successfully`,
        },
      ],
    };
  }

  async deployProject(args) {
    const deployment = await this.makeRequest(`/projects/${args.projectId}/deployments`, {
      method: 'POST',
      body: JSON.stringify({
        environment: args.environment || 'development',
      }),
    });

    return {
      content: [
        {
          type: 'text',
          text: `Deployment initiated:\n\n${JSON.stringify(deployment, null, 2)}`,
        },
      ],
    };
  }

  async getDeploymentStatus(args) {
    const endpoint = args.deploymentId 
      ? `/projects/${args.projectId}/deployments/${args.deploymentId}`
      : `/projects/${args.projectId}/deployments/latest`;

    const deployment = await this.makeRequest(endpoint);

    return {
      content: [
        {
          type: 'text',
          text: `Deployment status:\n\n${JSON.stringify(deployment, null, 2)}`,
        },
      ],
    };
  }

  async listEnvironments(args) {
    const environments = await this.makeRequest(`/projects/${args.projectId}/environments`);

    return {
      content: [
        {
          type: 'text',
          text: `Project environments:\n\n${JSON.stringify(environments, null, 2)}`,
        },
      ],
    };
  }

  async createEnvironment(args) {
    const environment = await this.makeRequest(`/projects/${args.projectId}/environments`, {
      method: 'POST',
      body: JSON.stringify({
        name: args.name,
        type: args.type,
        variables: args.variables || {},
      }),
    });

    return {
      content: [
        {
          type: 'text',
          text: `Environment created successfully:\n\n${JSON.stringify(environment, null, 2)}`,
        },
      ],
    };
  }

  async getLogs(args) {
    const params = new URLSearchParams({
      lines: args.lines || 100,
      follow: args.follow || false,
    });

    const endpoint = args.deploymentId 
      ? `/projects/${args.projectId}/deployments/${args.deploymentId}/logs?${params}`
      : `/projects/${args.projectId}/deployments/latest/logs?${params}`;

    const logs = await this.makeRequest(endpoint);

    return {
      content: [
        {
          type: 'text',
          text: `Deployment logs:\n\n${JSON.stringify(logs, null, 2)}`,
        },
      ],
    };
  }

  async run() {
    const transport = new StdioServerTransport();
    await this.server.connect(transport);
    console.error('Stitch MCP server running on stdio');
  }
}

const server = new StitchMCPServer();
server.run().catch(console.error);
