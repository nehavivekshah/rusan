! function (e) {
    "use strict";
    function t(t) {
        e(t).length > 0 && e(t).each((function () {
            var t = e(this).find("a");
            e(this).find(t).each((function () {
                e(this).on("click", (function () {
                    var t = e(this.getAttribute("href"));
                    t.length && (event.preventDefault(), e("html, body").stop().animate({
                        scrollTop: t.offset().top - 10
                    }, 1e3))
                }))
            }))
        }))
    }
    if (e(window).on("load", (function () {
            e(".preloader").fadeOut()
        })), e(".preloader").length > 0 && e(".preloaderCls").each((function () {
            e(this).on("click", (function (t) {
                t.preventDefault(), e(".preloader").css("display", "none")
            }))
        })), e.fn.thmobilemenu = function (t) {
            var n = e.extend({
                menuToggleBtn: ".th-menu-toggle",
                bodyToggleClass: "th-body-visible",
                subMenuClass: "th-submenu",
                subMenuParent: "th-item-has-children",
                subMenuParentToggle: "th-active",
                meanExpandClass: "th-mean-expand",
                appendElement: '<span class="th-mean-expand"></span>',
                subMenuToggleClass: "th-open",
                toggleSpeed: 400
            }, t);
            return this.each((function () {
                var t = e(this);
                function a() {
                    t.toggleClass(n.bodyToggleClass);
                    var a = "." + n.subMenuClass;
                    e(a).each((function () {
                        e(this).hasClass(n.subMenuToggleClass) && (e(this).removeClass(n.subMenuToggleClass), e(this).css("display", "none"), e(this).parent().removeClass(n.subMenuParentToggle))
                    }))
                }
                t.find("li").each((function () {
                    var t = e(this).find("ul");
                    t.addClass(n.subMenuClass), t.css("display", "none"), t.parent().addClass(n.subMenuParent), t.prev("a").append(n.appendElement), t.next("a").append(n.appendElement)
                }));
                var s = "." + n.meanExpandClass;
                e(s).each((function () {
                    e(this).on("click", (function (t) {
                        var a;
                        t.preventDefault(), a = e(this).parent(), e(a).next("ul").length > 0 ? (e(a).parent().toggleClass(n.subMenuParentToggle), e(a).next("ul").slideToggle(n.toggleSpeed), e(a).next("ul").toggleClass(n.subMenuToggleClass)) : e(a).prev("ul").length > 0 && (e(a).parent().toggleClass(n.subMenuParentToggle), e(a).prev("ul").slideToggle(n.toggleSpeed), e(a).prev("ul").toggleClass(n.subMenuToggleClass))
                    }))
                })), e(n.menuToggleBtn).each((function () {
                    e(this).on("click", (function () {
                        a()
                    }))
                })), t.on("click", (function (e) {
                    e.stopPropagation(), a()
                })), t.find("div").on("click", (function (e) {
                    e.stopPropagation()
                }))
            }))
        }, e(".th-menu-wrapper").thmobilemenu(), e.fn.thmobilemenu = function (t) {
            var n = e.extend({
                menuToggleBtn: ".th-menu-toggle2",
                bodyToggleClass: "th-body-visible",
                subMenuClass: "th-submenu",
                subMenuParent: "menu-item-has-children",
                thSubMenuParent: "th-item-has-children",
                subMenuParentToggle: "th-active",
                meanExpandClass: "th-mean-expand",
                appendElement: '<span class="th-mean-expand"></span>',
                subMenuToggleClass: "th-open",
                toggleSpeed: 400
            }, t);
            return this.each((function () {
                var t = e(this);
                function a() {
                    t.toggleClass(n.bodyToggleClass);
                    var a = "." + n.subMenuClass;
                    e(a).each((function () {
                        e(this).hasClass(n.subMenuToggleClass) && (e(this).removeClass(n.subMenuToggleClass), e(this).css("display", "none"), e(this).parent().removeClass(n.subMenuParentToggle))
                    }))
                }
                t.find("." + n.subMenuParent).each((function () {
                    var t = e(this).find("ul");
                    t.addClass(n.subMenuClass), t.css("display", "none"), e(this).addClass(n.subMenuParent), e(this).addClass(n.thSubMenuParent), e(this).children("a").append(n.appendElement)
                }));
                var s = "." + n.thSubMenuParent + " > a";
                e(s).each((function () {
                    e(this).on("click", (function (t) {
                        var a, s;
                        t.preventDefault(), a = e(this).parent(), (s = a.children("ul")).length > 0 && (a.toggleClass(n.subMenuParentToggle), s.slideToggle(n.toggleSpeed), s.toggleClass(n.subMenuToggleClass))
                    }))
                })), e(n.menuToggleBtn).each((function () {
                    e(this).on("click", (function () {
                        a()
                    }))
                })), t.on("click", (function (e) {
                    e.stopPropagation(), a()
                })), t.find("div").on("click", (function (e) {
                    e.stopPropagation()
                })), e(".th-mobile-menu a").on("click", (function () {
                    e(".th-menu-wrapper2").fadeOut(), e(".th-menu-wrapper2").removeClass("th-body-visible")
                })), e(".th-menu-toggle2").on("click", (function () {
                    e(".th-menu-wrapper2").fadeIn()
                }))
            }))
        }, e(".th-menu-wrapper2").thmobilemenu(), document.addEventListener("DOMContentLoaded", (function () {
            document.querySelectorAll(".menu-item-has-children > a").forEach(e => {
                e.addEventListener("click", (function (e) {
                    e.preventDefault();
                    let t = this.parentElement,
                        n = t.querySelector(".sub-menu");
                    n && (document.querySelectorAll(".sub-menu").forEach(e => {
                        e !== n && (e.style.maxHeight = null, e.parentElement.classList.remove("open"))
                    }), n.style.maxHeight ? (n.style.maxHeight = null, t.classList.remove("open")) : (n.style.maxHeight = n.scrollHeight + "px", t.classList.add("open")))
                }))
            })
        })), e(window).scroll((function () {
            e(this).scrollTop() > 500 ? e(".sticky-wrapper").addClass("sticky") : e(".sticky-wrapper").removeClass("sticky")
        })), t(".onepage-nav"), t(".scroll-down"), e(".scroll-top")) {
        var n = document.querySelector(".scroll-top"),
            a = document.querySelector(".scroll-top path"),
            s = a.getTotalLength();
        a.style.transition = a.style.WebkitTransition = "none", a.style.strokeDasharray = s + " " + s, a.style.strokeDashoffset = s, a.getBoundingClientRect(), a.style.transition = a.style.WebkitTransition = "stroke-dashoffset 10ms linear";
        var i = function () {
            var t = e(window).scrollTop(),
                n = e(document).height() - e(window).height(),
                i = s - t * s / n;
            a.style.strokeDashoffset = i
        };
        i(), e(window).scroll(i);
        jQuery(window).on("scroll", (function () {
            jQuery(this).scrollTop() > 50 ? jQuery(n).addClass("show") : jQuery(n).removeClass("show")
        })), jQuery(n).on("click", (function (e) {
            return e.preventDefault(), jQuery("html, body").animate({
                scrollTop: 0
            }, 750), !1
        }))
    }
    e("[data-bg-src]").length > 0 && e("[data-bg-src]").each((function () {
        var t = e(this).attr("data-bg-src");
        e(this).css("background-image", "url(" + t + ")"), e(this).removeAttr("data-bg-src").addClass("background-image")
    })), e("[data-bg-color]").length > 0 && e("[data-bg-color]").each((function () {
        var t = e(this).attr("data-bg-color");
        e(this).css("background-color", t), e(this).removeAttr("data-bg-color")
    })), e("[data-border]").each((function () {
        var t = e(this).data("border");
        e(this).css("--th-border-color", t)
    })), e("[data-mask-src]").length > 0 && e("[data-mask-src]").each((function () {
        var t = e(this).attr("data-mask-src");
        e(this).css({
            "mask-image": "url(" + t + ")",
            "-webkit-mask-image": "url(" + t + ")"
        }), e(this).addClass("bg-mask"), e(this).removeAttr("data-mask-src")
    })), e(".th-slider").each((function () {
        var t = e(this),
            n = e(this).data("slider-options"),
            a = t.find(".slider-prev"),
            s = t.find(".slider-next"),
            i = t.find(".slider-pagination.pagi-number"),
            o = t.siblings(".slider-controller").find(".slider-pagination"),
            l = o.length ? o.get(0) : t.find(".slider-pagination").get(0),
            r = o.length ? o.get(0) : t.find(".slider-pagination2").get(0),
            c = n.paginationType ? n.paginationType : "bullets",
            d = n.autoplay,
            u = {
                slidesPerView: 1,
                spaceBetween: n.spaceBetween ? n.spaceBetween : 24,
                loop: 0 != n.loop,
                speed: n.speed ? n.speed : 1e3,
                autoplay: d || {
                    delay: 6e3,
                    disableOnInteraction: !1
                },
                navigation: {
                    nextEl: s.get(0),
                    prevEl: a.get(0)
                },
                pagination: {
                    el: l,
                    type: c,
                    clickable: !0,
                    renderBullet: function (e, t) {
                        var n = e + 1,
                            a = n < 10 ? "0" + n : n;
                        return i.length ? '<span class="' + t + ' number">' + a + "</span>" : '<span class="' + t + '" aria-label="Go to Slide ' + a + '"></span>'
                    },
                    renderBullet: function (e, t) {
                        return '<span class="' + t + '">' + (e + 1) + "</span>"
                    },
                    formatFractionCurrent: function (e) {
                        return e < 10 ? "0" + e : e
                    },
                    formatFractionTotal: function (e) {
                        return e < 10 ? "0" + e : e
                    }
                },
                on: {
                    init: function () {
                        var t = this.slides.length;
                        e(r).html('<span class="current-slide">01</span> <span class="total-slides">' + (t < 10 ? "0" + t : t) + "</span>")
                    },
                    slideChange: function () {
                        var t = this.activeIndex + 1,
                            n = this.slides.length;
                        e(r).html('<span class="current-slide">' + (t < 10 ? "0" + t : t) + '</span> <span class="total-slides">' + (n < 10 ? "0" + n : n) + "</span>")
                    }
                }
            },
            p = JSON.parse(t.attr("data-slider-options"));
        p = e.extend({}, u, p);
        new Swiper(t.get(0), p);
        e(".slider-area").length > 0 && e(".slider-area").closest(".container").parent().addClass("arrow-wrap")
    })), e("[data-ani]").each((function () {
        var t = e(this).data("ani");
        e(this).addClass(t)
    })), e("[data-ani-delay]").each((function () {
        var t = e(this).data("ani-delay");
        e(this).css("animation-delay", t)
    })), e("[data-slider-prev], [data-slider-next]").on("click", (function () {
        var t = e(this).data("slider-prev") || e(this).data("slider-next"),
            n = e(t);
        if (n.length) {
            var a = n[0].swiper;
            a && (e(this).data("slider-prev") ? a.slidePrev() : a.slideNext())
        }
    })), e.fn.activateSliderThumbs = function (t) {
        var n = e.extend({
            sliderTab: !1,
            tabButton: ".tab-btn"
        }, t);
        return this.each((function () {
            var t = e(this),
                a = t.find(n.tabButton),
                s = e('<span class="indicator"></span>').appendTo(t),
                i = t.data("slider-tab"),
                o = e(i)[0].swiper;
            if (a.on("click", (function (t) {
                    t.preventDefault();
                    var a = e(this);
                    if (a.addClass("active").siblings().removeClass("active"), c(a), n.sliderTab) {
                        var s = a.index();
                        o.slideTo(s)
                    }
                })), n.sliderTab) {
                o.on("slideChange", (function () {
                    var e = o.realIndex,
                        t = a.eq(e);
                    t.addClass("active").siblings().removeClass("active"), c(t)
                }));
                var l = o.activeIndex,
                    r = a.eq(l);
                r.addClass("active").siblings().removeClass("active"), c(r)
            }
            function c(e) {
                var t = e.position(),
                    n = parseInt(e.css("margin-top")) || 0,
                    a = parseInt(e.css("margin-left")) || 0;
                s.css("--height-set", e.outerHeight() + "px"), s.css("--width-set", e.outerWidth() + "px"), s.css("--pos-y", t.top + n + "px"), s.css("--pos-x", t.left + a + "px")
            }
        }))
    }, e(".testi-thumb").length && e(".testi-thumb").activateSliderThumbs({
        sliderTab: !0,
        tabButton: ".tab-btn"
    }), e(".hero-thumb").length && e(".hero-thumb").activateSliderThumbs({
        sliderTab: !0,
        tabButton: ".tab-btn"
    });
    var o, l, r, c = ".ajax-contact",
        d = '[name="email"]',
        u = e(".form-messages");
    function p() {
        var t = e(c).serialize();
        (function () {
            var t, n = !0;
            function a(a) {
                a = a.split(",");
                for (var s = 0; s < a.length; s++) t = c + " " + a[s], e(t).val() ? (e(t).removeClass("is-invalid"), n = !0) : (e(t).addClass("is-invalid"), n = !1)
            }
            a('[name="name"],[name="email"],[name="subject"],[name="number"],[name="message"]'), e(d).val() && e(d).val().match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/) ? (e(d).removeClass("is-invalid"), n = !0) : (e(d).addClass("is-invalid"), n = !1);
            return n
        })() && jQuery.ajax({
            url: e(c).attr("action"),
            data: t,
            type: "POST"
        }).done((function (t) {
            u.removeClass("error"), u.addClass("success"), u.text(t), e(c + ' input:not([type="submit"]),' + c + " textarea").val("")
        })).fail((function (e) {
            u.removeClass("success"), u.addClass("error"), "" !== e.responseText ? u.html(e.responseText) : u.html("Oops! An error occured and your message could not be sent.")
        }))
    }
    if (e(c).on("submit", (function (e) {
            e.preventDefault(), p()
        })), o = ".popup-search-box", l = ".searchClose", r = "show", e(".searchBoxToggler").on("click", (function (t) {
            t.preventDefault(), e(o).addClass(r)
        })), e(o).on("click", (function (t) {
            t.stopPropagation(), e(o).removeClass(r)
        })), e(o).find("form").on("click", (function (t) {
            t.stopPropagation(), e(o).addClass(r)
        })), e(l).on("click", (function (t) {
            t.preventDefault(), t.stopPropagation(), e(o).removeClass(r)
        })), function (t, n, a, s) {
            e(n).on("click", (function (n) {
                n.preventDefault(), e(t).addClass(s)
            })), e(t).on("click", (function (n) {
                n.stopPropagation(), e(t).removeClass(s)
            })), e(t + " > div").on("click", (function (n) {
                n.stopPropagation(), e(t).addClass(s)
            })), e(a).on("click", (function (n) {
                n.preventDefault(), n.stopPropagation(), e(t).removeClass(s)
            }))
        }(".sidemenu-wrapper", ".sideMenuToggler", ".sideMenuCls", "show"), e(".popup-image").magnificPopup({
            type: "image",
            mainClass: "mfp-zoom-in",
            removalDelay: 260,
            gallery: {
                enabled: !0
            }
        }), e(".popup-video").magnificPopup({
            type: "iframe",
            mainClass: "mfp-zoom-in",
            removalDelay: 260
        }), e(".popup-content").magnificPopup({
            type: "inline",
            midClick: !0
        }), e(".th-screen").length && e(window).on("scroll", (function () {
            ! function (t, n = 0) {
                var a = e(window).scrollTop(),
                    s = a + e(window).height(),
                    i = e(t).offset().top;
                return i + e(t).height() - parseInt(n) <= s && i >= a
            }(".th-screen", 200) ? e(".th-screen").removeClass("th-visible"): e(".th-screen").addClass("th-visible")
        })), e(".th-anim").length) {
        gsap.registerPlugin(ScrollTrigger), document.querySelectorAll(".th-anim").forEach(e => {
            let t = e.querySelector("img"),
                n = gsap.timeline({
                    scrollTrigger: {
                        trigger: e,
                        toggleActions: "play none none none"
                    }
                });
            n.set(e, {
                autoAlpha: 1
            }), n.from(e, 1.5, {
                xPercent: -100,
                ease: Power2.out
            }), n.from(t, 1.5, {
                xPercent: 100,
                scale: 1.3,
                delay: -1.5,
                ease: Power2.out
            })
        })
    }
    new Swiper(".processSlide", {
        spaceBetween: 30,
        centeredSlides: !0,
        autoplay: {
            delay: 6e3,
            disableOnInteraction: !1
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: !0
        },
        on: {
            autoplayTimeLeft: function (e, t, n) {
                var a = document.querySelector(".progress-bar"),
                    s = document.querySelector(".progress-text"),
                    i = Math.floor(100 * n),
                    o = 125.6 * (1 - n);
                a.style.strokeDashoffset = o, s.textContent = i + "%", 1 === n && (s.textContent = "100%")
            }
        }
    });
    function h(e) {
        return parseInt(e, 10)
    }
    e.fn.sectionPosition = function (t, n) {
        e(this).each((function () {
            var a, s, i, o, l, r = e(this);
            a = Math.floor(r.height() / 2), s = r.attr(t), i = r.attr(n), o = h(e(i).css("padding-top")), l = h(e(i).css("padding-bottom")), "top-half" === s ? (e(i).css("padding-bottom", l + a + "px"), r.css("margin-top", "-" + a + "px")) : "bottom-half" === s && (e(i).css("padding-top", o + a + "px"), r.css("margin-bottom", "-" + a + "px"))
        }))
    };
    function g(t, n, a, s) {
        var i = t.text().split(n),
            o = "";
        i.length && (e(i).each((function (e, t) {
            o += '<span class="' + a + (e + 1) + '">' + t + "</span>" + s
        })), t.empty().append(o))
    }
    e("[data-sec-pos]").length && e("[data-sec-pos]").imagesLoaded((function () {
        e("[data-sec-pos]").sectionPosition("data-sec-pos", "data-pos-for")
    })), e(".filter-active").imagesLoaded((function () {
        if (e(".filter-active").length > 0) {
            var t = e(".filter-active").isotope({
                itemSelector: ".filter-item",
                filter: "*",
                masonry: {
                    columnWidth: 1
                }
            });
            e(".filter-menu-active").on("click", "button", (function () {
                var n = e(this).attr("data-filter");
                t.isotope({
                    filter: n
                })
            })), e(".filter-menu-active").on("click", "button", (function (t) {
                t.preventDefault(), e(this).addClass("active"), e(this).siblings(".active").removeClass("active")
            }))
        }
    })), e(".filter-active-cat1").imagesLoaded((function () {
        if (e(".filter-active-cat1").length > 0) {
            var t = e(".filter-active-cat1").isotope({
                itemSelector: ".filter-item",
                filter: ".cat1",
                masonry: {
                    columnWidth: 1
                }
            });
            e(".filter-menu-active").on("click", "button", (function () {
                var n = e(this).attr("data-filter");
                t.isotope({
                    filter: n
                })
            })), e(".filter-menu-active").on("click", "button", (function (t) {
                t.preventDefault(), e(this).addClass("active"), e(this).siblings(".active").removeClass("active")
            }))
        }
    })), e(".masonary-active").imagesLoaded((function () {
        e(".masonary-active").length > 0 && e(".masonary-active").isotope({
            itemSelector: ".filter-item",
            filter: "*",
            masonry: {
                columnWidth: 1
            }
        })
    })), e(".masonary-active, .woocommerce-Reviews .comment-list").imagesLoaded((function () {
        var t = ".masonary-active, .woocommerce-Reviews .comment-list";
        e(t).length > 0 && e(t).isotope({
            itemSelector: ".filter-item, .woocommerce-Reviews .comment-list li",
            filter: "*",
            masonry: {
                columnWidth: 1
            }
        }), e('[data-bs-toggle="tab"]').on("shown.bs.tab", (function (n) {
            e(t).isotope({
                filter: "*"
            })
        }))
    })), e(".counter-number").counterUp({
        delay: 10,
        time: 1e3
    });
    var m = {
        init: function () {
            return this.each((function () {
                g(e(this), "", "char", "")
            }))
        },
        words: function () {
            return this.each((function () {
                g(e(this), " ", "word", " ")
            }))
        },
        lines: function () {
            return this.each((function () {
                var t = "eefec303079ad17405c889e092e105b0";
                g(e(this).children("br").replaceWith(t).end(), t, "line", "")
            }))
        }
    };
    function f() {
        e(".feature-circle .progressbar").each((function () {
            var t = e(this).attr("data-path-color"),
                n = e(this).offset().top,
                a = e(window).scrollTop(),
                s = e(this).find(".circle").attr("data-percent"),
                i = (parseInt(s, 10), parseInt(100, 10), e(this).data("animate"));
            n < a + e(window).height() - 30 && !i && (e(this).data("animate", !0), e(this).find(".circle").circleProgress({
                startAngle: -Math.PI / 2,
                value: s / 100,
                size: 100,
                thickness: 6,
                emptyFill: "transparent",
                lineCap: "round",
                fill: {
                    color: t
                }
            }).on("circle-animation-progress", (function (t, n, a) {
                e(this).find(".circle-num").text((100 * a).toFixed(0) + "%")
            })).stop())
        })), e(".skill-circle .progressbar").each((function () {
            var t = e(this).attr("data-path-color"),
                n = e(this).offset().top,
                a = e(window).scrollTop(),
                s = e(this).find(".circle").attr("data-percent"),
                i = (parseInt(s, 10), parseInt(100, 10), e(this).data("animate"));
            n < a + e(window).height() - 30 && !i && (e(this).data("animate", !0), e(this).find(".circle").circleProgress({
                startAngle: -Math.PI / 2,
                value: s / 100,
                size: 176,
                thickness: 8,
                emptyFill: "#EFF1F9",
                lineCap: "round",
                fill: {
                    color: t
                }
            }).on("circle-animation-progress", (function (t, n, a) {
                e(this).find(".circle-num").text((100 * a).toFixed(0) + "%")
            })).stop())
        }))
    }
    e.fn.lettering = function (t) {
        return t && m[t] ? m[t].apply(this, [].slice.call(arguments, 1)) : "letters" !== t && t ? (e.error("Method " + t + " does not exist on jQuery.lettering"), this) : m.init.apply(this, [].slice.call(arguments, 0))
    }, e(".discount-anime").lettering(), e.fn.shapeMockup = function () {
        e(this).each((function () {
            var t = e(this),
                n = t.data("top"),
                a = t.data("right"),
                s = t.data("bottom"),
                i = t.data("left");
            t.css({
                top: n,
                right: a,
                bottom: s,
                left: i
            }).removeAttr("data-top").removeAttr("data-right").removeAttr("data-bottom").removeAttr("data-left").parent().addClass("shape-mockup-wrap")
        }))
    }, e(".shape-mockup") && e(".shape-mockup").shapeMockup(), e(".progress-bar").waypoint((function () {
        e(".progress-bar").css({
            animation: "animate-positive 1.8s",
            opacity: "1"
        })
    }), {
        offset: "75%"
    }), e(".price_slider").slider({
        range: !0,
        min: 10,
        max: 100,
        values: [10, 75],
        slide: function (t, n) {
            e(".from").text("$" + n.values[0]), e(".to").text("$" + n.values[1])
        }
    }), e(".from").text("$" + e(".price_slider").slider("values", 0)), e(".to").text("$" + e(".price_slider").slider("values", 1)), e(".tilt-active").tilt({
        maxTilt: 7,
        perspective: 1e3
    }), f(), e(window).scroll(f), e(".hover-item").hover((function () {
        e(this).addClass("item-active"), e(this).siblings().removeClass("item-active")
    })), e("#ship-to-different-address-checkbox").on("change", (function () {
        e(this).is(":checked") ? e("#ship-to-different-address").next(".shipping_address").slideDown() : e("#ship-to-different-address").next(".shipping_address").slideUp()
    })), e(".woocommerce-form-login-toggle a").on("click", (function (t) {
        t.preventDefault(), e(".woocommerce-form-login").slideToggle()
    })), e(".woocommerce-form-coupon-toggle a").on("click", (function (t) {
        t.preventDefault(), e(".woocommerce-form-coupon").slideToggle()
    })), e(".shipping-calculator-button").on("click", (function (t) {
        t.preventDefault(), e(this).next(".shipping-calculator-form").slideToggle()
    })), e('.wc_payment_methods input[type="radio"]:checked').siblings(".payment_box").show(), e('.wc_payment_methods input[type="radio"]').each((function () {
        e(this).on("change", (function () {
            e(".payment_box").slideUp(), e(this).siblings(".payment_box").slideDown()
        }))
    })), e(".rating-select .stars a").each((function () {
        e(this).on("click", (function (t) {
            t.preventDefault(), e(this).siblings().removeClass("active"), e(this).parent().parent().addClass("selected"), e(this).addClass("active")
        }))
    })), e(".quantity-plus").each((function () {
        e(this).on("click", (function (t) {
            t.preventDefault();
            var n = e(this).siblings(".qty-input"),
                a = parseInt(n.val(), 10);
            isNaN(a) || n.val(a + 1)
        }))
    })), e(".quantity-minus").each((function () {
        e(this).on("click", (function (t) {
            t.preventDefault();
            var n = e(this).siblings(".qty-input"),
                a = parseInt(n.val(), 10);
            !isNaN(a) && a > 1 && n.val(a - 1)
        }))
    }));
    var v = e(".cursor"),
        b = e(".cursor2"),
        y = 0,
        C = 0,
        w = 0,
        k = 0;
    TweenMax.to({}, .016, {
        repeat: -1,
        onRepeat: function () {
            y += (w - y) / 9, C += (k - C) / 9, TweenMax.set(b, {
                css: {
                    left: y - 12,
                    top: C - 12
                }
            }), TweenMax.set(v, {
                css: {
                    left: w,
                    top: k
                }
            })
        }
    }), e(document).on("mousemove", (function (e) {
        w = e.clientX, k = e.clientY
    })), e(".btn").on("mouseenter", (function () {
        v.addClass("active"), b.addClass("active")
    })), e(".btn").on("mouseleave", (function () {
        v.removeClass("active"), b.removeClass("active")
    })), window.gsap.registerPlugin(window.TweenMax), e(".color-switch-btns button").each((function () {
        const t = e(this),
            n = t.data("color");
        t.css("--theme-color", n), t.on("click", (function () {
            const t = e(this).data("color");
            e("body").css("--theme-color", t)
        }))
    })), e(document).on("click", ".switchIcon", (function () {
        e(".color-scheme-wrap").toggleClass("active")
    }));
    var x = document.getElementById("filt-monthly"),
        T = document.getElementById("filt-yearly"),
        M = document.getElementById("switcher"),
        P = document.getElementById("monthly"),
        S = document.getElementById("yearly");
    e(".pricing-tabs").length && (x.addEventListener("click", (function () {
        M.checked = !1, x.classList.add("toggler--is-active"), T.classList.remove("toggler--is-active"), P.classList.remove("hide"), S.classList.add("hide")
    })), T.addEventListener("click", (function () {
        M.checked = !0, T.classList.add("toggler--is-active"), x.classList.remove("toggler--is-active"), P.classList.add("hide"), S.classList.remove("hide")
    })), M.addEventListener("click", (function () {
        T.classList.toggle("toggler--is-active"), x.classList.toggle("toggler--is-active"), P.classList.toggle("hide"), S.classList.toggle("hide")
    }))),
    // window.addEventListener("contextmenu", (function (e) {
    //     e.preventDefault()
    // }), !1), 
    document.onkeydown = function (e) {
        return 123 != event.keyCode && ((!e.ctrlKey || !e.shiftKey || e.keyCode != "I".charCodeAt(0)) && ((!e.ctrlKey || !e.shiftKey || e.keyCode != "C".charCodeAt(0)) && ((!e.ctrlKey || !e.shiftKey || e.keyCode != "J".charCodeAt(0)) && ((!e.ctrlKey || e.keyCode != "U".charCodeAt(0)) && void 0))))
    }
}(jQuery);