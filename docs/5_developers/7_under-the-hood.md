---
title: Under the Hood
description: Technical details about how DreamForm works
---

Learn about the internal workings of DreamForm, including the submission lifecycle, validation process, and architecture decisions.

## Submission Lifecycle

### Regular Submission

1. **Request Reception**: Form receives POST request
2. **Session Initialization**: Creates or retrieves submission session
3. **Guard Execution**: Security guards validate the request
4. **Field Validation**: Each field validates its value
5. **Hook Execution**: `dreamform.submit:before` hooks run
6. **Action Processing**: Actions execute in defined order
7. **Submission Storage**: Data saved to submissions folder
8. **Success Handling**: Redirects or displays success message
9. **Hook Completion**: `dreamform.submit:after` hooks run

### Precognition Lifecycle

When precognition is enabled, field validation requests follow a simplified flow:

1. **Precognitive Request**: Receives POST with `?precognition=true` (through HTMX)
2. **Minimal Guards**: Only precognitive guards execute
3. **Single Field Validation**: Only the triggering field is validated
4. **Response Generation**: Returns updated field HTML
5. **No Side Effects**: No hooks, actions, or data storage
