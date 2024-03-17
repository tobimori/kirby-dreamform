## Actions

### What are Actions?

Actions are ran after form submission & field validation. They can utilize the submitted field data, change them, or ultimately still reject the submission.

Combining these actions, you can create complex, multi-dimensional workflows for your forms, without leaving the Kirby Panel.

### Available Actions

#### Discord Action

The Discord action allows you to send answers of a submission to a Discord channel by utilizing [Webhooks](https://discord.com/developers/docs/resources/webhook).

1. Choose the channel you want to receive notifications in and click on "Edit Channel".

2. Click on "Integrations" in the sidebar and then on "Webhooks".

3. Create a new webhook, set a name and optionally a profile picture and click on "Copy Webhook URL".

4. Go to the panel and open the form you want to add the Discord action to.

5. Click on "Workflow", then on "Add" and select "Send Discord Message".

6. Paste the Webhook URL into the "Webhook URL" field. You can also specify a default webhook URL in your `config.php` file. (See below)

7. Optionally, you can customize the exposed fields that are sent to Discord.
