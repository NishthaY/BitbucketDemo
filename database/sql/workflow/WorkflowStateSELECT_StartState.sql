SELECT
  ws.*
FROM
  "Workflow" w
  join "WorkflowState" ws on ( ws."WorkflowId" = w."Id")
  join "WorkflowStateOrder" wo on ( wo."WorkflowId" = w."Id" and wo."WorkflowStateId" = ws."Id" )
WHERE
  w."Id" = ?
order BY
  wo."SortOrder" asc
limit 1