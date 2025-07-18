class Config {
    adjustLayout() {
      const i = document.getElementsByTagName("html")[0];
      if (window.innerWidth <= 1200) {
        i.setAttribute("data-sidebar-size", "full");
      }
    }
  
    initWindowSize() {
      var t = this;
      window.addEventListener("resize", function(i) {
        t.adjustLayout();
      });
    }
  
    init() {
      this.adjustLayout();
      this.initWindowSize();
    }
  }
  
  (new Config).init();
  