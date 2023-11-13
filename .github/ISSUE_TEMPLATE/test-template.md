---
name: Test template
about: Describe this issue template's purpose here.
title: ''
labels: ''
assignees: ''

---

name: 🐞 Report a new Bug
description: If you found a new bug, report it here.
title: "[BUG]: "
body:
  - type: markdown
    attributes:
      value: |

        Thank you for submitting a bug report 💚  

        👉 Before you submit a new bug, please do the following steps:

        1. Update to [latest version](https://mary-ui.com/docs/upgrading) 

  - type: input
    id: version
    attributes:
      label: What version of MaryUI?
      description: You can see the MaryUI version number on your `composer.json` file.
      placeholder: "example: 1.3.1"
    validations:
      required: true

  - type: dropdown
    id: browsers
    attributes:
      label: Which browsers are you seeing the problem on?
      multiple: true
      options:
        - Chrome
        - Safari
        - Firefox
        - Edge
        - Other

  - type: textarea
    id: description
    attributes:
      label: Describe your issue
      description: |

        ℹ️ Describe the problem and say how and when it happens.

        ℹ️ To write a code block, use ``` before and after your code.

        ℹ️ Please don't paste code screenshots, past code as text!

    validations:
      required: true
