# Slack Action

The Slack action sends submission details to a Slack channel.

## Getting Started

1. [Create a new Slack API app](https://api.slack.com/apps/) in your Slack account & workspace.

2. Choose "From an app manifest", and select your workspace.

3. Paste in the following app manifest, continue & click on "Create".

```json
{
	"display_information": {
		"name": "Kirby DreamForm"
	},
	"features": {
		"bot_user": {
			"display_name": "Kirby",
			"always_online": false
		}
	},
	"oauth_config": {
		"scopes": {
			"bot": ["incoming-webhook"]
		}
	},
	"settings": {
		"org_deploy_enabled": false,
		"socket_mode_enabled": false,
		"token_rotation_enabled": false
	}
}
```

3. Click on "Features" > "Incoming Webhooks" and "Add new Webhook to Workspace".

4. Copy the webhook URL.
