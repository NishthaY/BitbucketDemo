update "WorkflowProgress"
set
    "Waiting" = true
where
    "Identifier" = ?
    and "IdentifierType" = ?
    and "WorkflowId" = ?
    and "WorkflowStateId" = ?