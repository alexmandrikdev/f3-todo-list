function onCompletedCheckboxChange(todoId, element) {
  $.ajax({
    url: "/todos/toggleCompleted",
    type: "PUT",
    data: {
      todoId,
      completed: element.checked,
    },
    success: (res) => {
      // console.log(JSON.parse(res));

      location.reload();
    },
  });
}

function initializeFlatpickr(selector, additionalOptions = {}) {
  return flatpickr(selector, {
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    time_24hr: true,
    ...additionalOptions,
  });
}

initializeFlatpickr(".date-picker");

function setInputValueAndSubmitForm(inputSelector, value) {
  $(inputSelector).val(value).closest("form").submit();
}

function addDeadlineToTodo(todoId) {
  let deadline = now();

  $(`#todo_${todoId}_deadline`).append(deadline);

  saveDeadline(todoId, deadline);
}

let tmpDatepickerExists = false;

/**
 * Set the element's innerHTML to a datepicker. And set the datepicker's defaultDate and onClose options.
 *
 * @param {int} todoId
 * @param {string} deadline
 * @param {object} element
 */
function addDatepicker(todoId, deadline, element) {
  element.innerHTML = `<input type="text" id="tmp-date-picker" style="width: 150px;" class="form-control date-picker">`;

  tmpDatepickerExists = true;

  initializeFlatpickr("#tmp-date-picker", {
    defaultDate: deadline,
    onClose: (selectedDates, dateStr) => {
      if (deadline !== dateStr) {
        saveDeadline(todoId, dateStr);
      }
    },
  });
}

function saveDeadline(todoId, deadline) {
  $.ajax({
    url: `/todos/${todoId}/updateDeadline`,
    type: "PUT",
    data: {
      deadline,
    },
    success: (res) => {
      // console.log(JSON.parse(res));

      location.reload();
    },
  });
}

/**
 * Remove the temporary datepicker if clicked outside
 */
$(document).click((e) => {
  if (
    e.target.id !== "tmp-date-picker" &&
    !e.target.classList.contains("deadline") &&
    tmpDatepickerExists
  ) {
    const tmpDatepicker = $("#tmp-date-picker");

    let deadline = tmpDatepicker.val();

    tmpDatepicker.closest(".deadline").html(deadline);
  }
});

function now() {
  let today = new Date();

  let day = today.getDate();

  let month = today.getMonth() + 1;

  let year = today.getFullYear();

  let hours = today.getHours();

  let minutes = today.getMinutes();

  if (day < 10) {
    day = "0" + day;
  }

  if (month < 10) {
    month = "0" + month;
  }

  if (hours < 10) {
    hours = "0" + hours;
  }

  if (minutes < 10) {
    minutes = "0" + minutes;
  }

  return `${year}-${month}-${day} ${hours}:${minutes}:00`;
}

/**
 * Enable tooltip for all elements.
 */
var tooltipTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});
