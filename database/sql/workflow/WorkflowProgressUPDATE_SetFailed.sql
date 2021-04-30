update "WorkflowProgress"
set
    "Failed" = true
where
    "Identifier" = ?
    and "IdentifierType" = ?
    and "WorkflowId" = ?
    and "WorkflowStateId" = ?