(function () {
  const STATUS_STEPS = ["pending", "preparing", "ready", "delivered"];

  const STATUS_LABELS = {
    pending: "Pending",
    preparing: "Preparing",
    ready: "Ready",
    delivered: "Delivered",
    cancelled: "Cancelled"
  };

  const STATUS_BADGE_CLASSES = [
    "badge-warning",
    "badge-accent",
    "badge-soft",
    "badge-success",
    "badge-danger"
  ];

  function labelStatus(status) {
    return STATUS_LABELS[status] || status;
  }

  function badgeClass(status) {
    const map = {
      pending: "badge-warning",
      preparing: "badge-accent",
      ready: "badge-soft",
      delivered: "badge-success",
      cancelled: "badge-danger"
    };

    return map[status] || "badge-soft";
  }

  function updateBadge(orderId, status) {
    document.querySelectorAll(`[data-order-status-badge="${orderId}"]`).forEach((badge) => {
      STATUS_BADGE_CLASSES.forEach((className) => badge.classList.remove(className));
      badge.classList.add(badgeClass(status));

      if (badge.classList.contains("status-big")) {
        badge.textContent = `Current status: ${labelStatus(status)}`;
      } else {
        badge.textContent = `● ${labelStatus(status)}`;
      }
    });
  }

  function updateTracker(orderId, status) {
    const currentIndex = STATUS_STEPS.indexOf(status);

    document.querySelectorAll(`[data-order-tracker="${orderId}"]`).forEach((tracker) => {
      tracker.querySelectorAll(".track-step").forEach((step, index) => {
        step.classList.remove("done", "active");

        if (currentIndex === -1) {
          return;
        }

        if (index < currentIndex) {
          step.classList.add("done");
        }

        if (index === currentIndex) {
          step.classList.add("active");
        }
      });
    });
  }

  function updateAdminRow(orderId, status) {
    document.querySelectorAll(`[data-order-row="${orderId}"]`).forEach((row) => {
      row.dataset.status = status;
    });
  }

  function updateCounts(counts) {
    if (!counts) return;

    Object.entries(counts).forEach(([status, total]) => {
      document.querySelectorAll(`[data-count-status="${status}"]`).forEach((target) => {
        target.textContent = total;
      });
    });
  }

  async function refresh(options = {}) {
    const scope = options.scope || "customer";
    const params = new URLSearchParams({ scope });

    if (options.orderId) {
      params.set("order_id", options.orderId);
    }

    const response = await fetch(`live-order-status.php?${params.toString()}`, {
      headers: {
        Accept: "application/json"
      },
      cache: "no-store"
    });

    if (!response.ok) {
      return null;
    }

    const payload = await response.json();

    if (!payload.success) {
      return null;
    }

    payload.orders.forEach((order) => {
      updateBadge(order.order_id, order.order_status);
      updateTracker(order.order_id, order.order_status);
      updateAdminRow(order.order_id, order.order_status);
    });

    updateCounts(payload.counts);

    document.dispatchEvent(new CustomEvent("furrfect:status-updated", {
      detail: payload
    }));

    return payload;
  }

  function start(options = {}) {
    const interval = options.interval || 3000;

    refresh(options);
    return window.setInterval(() => refresh(options), interval);
  }

  window.FurrfectLiveStatus = {
    start,
    refresh
  };
})();
