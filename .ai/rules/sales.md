---
paths:
  - 'app/Domains/Sales/**'
---

# Sales

## Clients use repository + service; Leads/Estimates use Actions
The Client module uses the repository + service pattern (`Repositories/ClientRepository` + `Services/ClientService`), per the Client prompt. Leads and Estimates in this domain still use the Action-class pattern. Controllers and Volt components depend on `ClientService`, never the model/repository directly.
