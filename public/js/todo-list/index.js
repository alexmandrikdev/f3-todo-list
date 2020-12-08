function onCheckboxChecked(todoId, element) {
  $.ajax({
    url: "/todos",
    type: "POST",
    data: {
      todoId,
      completed: element.checked,
    },
  });
}
