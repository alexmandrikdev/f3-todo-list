function onCheckboxChecked(todoId, element) {
  $.ajax({
    url: "/todos/toggleCompleted",
    type: "PUT",
    data: {
      todoId,
      completed: element.checked,
    },
    // success: (res) => console.log(res),
  });
}
