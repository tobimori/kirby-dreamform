## 📄 Developers Checklist - Going Live

DreamForm is a powerful tool for creating forms in Kirby and offers a lot of functionality out of the box. In order for users & editors to have the best experience and make DreamForm feel as native as possible, check the following list before going live with your project:

1. Check if all available fields have a snippet and/or are properly styled to fit with the rest of the form. I generally recommend to disable fields that are not necessary for your project, so that your editors don't get confused by the amount of options & if they work or net.

2. Check if all available actions are necessary for your project, or disable them to avoid confusion. Also make sure that fields using third-party services are properly configured and working. Check email credentials for the Email action to work as well.

3. Check if the correct guards are set for your site. Some guards might not work as expected for your stack. Prominent examples would be the CSRF guard in combination with a cached page or the Rate Limit guard in combination with a CDN or reverse proxy. Make sure to test your forms in a production-like environment.

4. If you're using the PRG submission mode, make sure that the cache is disabled for pages that contain forms. Otherwise, the results of a submission (errors, success) might not be displayed correctly. Multi-step forms also require PRG mode to work correctly.

5. If you're using the API submission mode, make sure that your script correctly notifies the user of any errors or submission success.

6. Using any custom logic, custom guards, fields or actions? Make sure they are properly tested and working as expected.

7. You should be ready to go live with your project now. If you encounter any issues, feel free to open an issue on the GitHub repository or ask for help in the Kirby Discord server.
