---
paths:
  - 'app/Ai/Tools/Marketing/**'
---

# Marketing

## Marketing campaigns are platform-wide, not company-scoped
marketing_campaigns, marketing_campaign_steps and marketing_email_logs have no company_id column, so the shared findRecord() tenant lookup throws for them. Resolve campaigns with findMarketingCampaign() (UUID, name or id) and rely on the manage_marketing policy for access control.
