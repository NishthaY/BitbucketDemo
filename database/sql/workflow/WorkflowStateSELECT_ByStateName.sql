SELECT
  ws.*
FROM
  "Workflow" w
  join "WorkflowState" ws on ( ws."WorkflowId" = w."Id")
WHERE
  w."Id" = ?
  and ws."Name" = ?
