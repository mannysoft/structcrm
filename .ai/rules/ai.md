---
paths:
  - 'app/Ai/**'
---

# Ai

## AI tools: company scoping, recoverable failures, approvals
Every tool under app/Ai/Tools must use InteractsWithCompanyData and query with `where('company_id', $this->companyId())` — never rely on the ambient auth() tenant scope (tools also run from the API). Resolve records with findRecord() (UUID, id, or natural number) and run work inside attempt() so ToolFailure becomes {"ok":false,...} instead of aborting the run; only ValidationException may escape. Mutating tools that delete data, move money, approve change orders or grant access must implement Laravel\Ai\Contracts\Approvable with InteractsWithApprovals.
