select "Input", "Output" from
  (
    select "Input", "Output" from "ObjectMapping" where "Code" = ?
    UNION
    select upper("Input"), "Output" from "ObjectMapping" where "Code" = ?
  ) as t
order by "Input" asc