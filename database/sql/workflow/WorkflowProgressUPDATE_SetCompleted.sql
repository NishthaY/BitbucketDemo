update "WorkflowProgress"
set
  "Complete" = true
where
  "Identifier" = ?
  and "IdentifierType" = ?
  and "WorkflowId" = ?
  and "WorkflowStateId" = ?