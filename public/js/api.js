const API = (() => {
  const BASE = "/api";

  async function request(endpoint, options = {}) {
    const url = `${BASE}${endpoint}`;
    const defaults = {
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
    };
    const config = { ...defaults, ...options };

    if (config.body && typeof config.body === "object") {
      config.body = JSON.stringify(config.body);
    }

    try {
      const res = await fetch(url, config);
      const data = await res.json();
      return data;
    } catch (err) {
      console.error("API request failed:", endpoint, err);
      return { success: false, message: "Network error. Please try again." };
    }
  }

  function checkSession() {
    return request("/check_session.php");
  }

  function login(username, password) {
    return request("/login.php", {
      method: "POST",
      body: { username, password },
    });
  }

  function register(username, email, password) {
    return request("/register.php", {
      method: "POST",
      body: { username, email, password },
    });
  }

  function logout() {
    return request("/logout.php", { method: "POST" });
  }

  function saveScore({ score, correct, total, difficulty, best_streak, gens }) {
    return request("/save_score.php", {
      method: "POST",
      body: { score, correct, total, difficulty, best_streak, gens },
    });
  }

  function getLeaderboard(limit = 20, difficulty) {
    const diffParam = difficulty ? `&difficulty=${difficulty}` : ""; return request(`/leaderboard.php?limit=${limit}${diffParam}`);
  }

  function getUserStats(userId) {
    return request(`/user_stats.php?user_id=${userId}`);
  }

  function getUsers() {
    return request("/users.php");
  }

  function setUserStatus(userId, status) {
    return request("/disable_user.php", {
      method: "POST",
      body: { user_id: userId, status },
    });
  }

  return {
    checkSession,
    login,
    register,
    logout,
    saveScore,
    getLeaderboard,
    getUserStats,
    getUsers,
    setUserStatus,
  };
})();
