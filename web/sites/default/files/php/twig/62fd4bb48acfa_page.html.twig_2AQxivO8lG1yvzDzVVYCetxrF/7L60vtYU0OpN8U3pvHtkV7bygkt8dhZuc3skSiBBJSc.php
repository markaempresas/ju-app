<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* themes/custom/jutheme/templates/layout/page.html.twig */
class __TwigTemplate_85bb578449d6117ead09fff42789cb442b75fa7fa428ef73f5647a630957174b extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 46
        $context["nav_classes"] = ((("navbar navbar-expand-lg" . (((        // line 47
($context["b5_navbar_schema"] ?? null) != "none")) ? ((" navbar-" . $this->sandbox->ensureToStringAllowed(($context["b5_navbar_schema"] ?? null), 47, $this->source))) : (" "))) . (((        // line 48
($context["b5_navbar_schema"] ?? null) != "none")) ? ((((($context["b5_navbar_schema"] ?? null) == "dark")) ? (" text-light") : (" text-dark"))) : (" "))) . (((        // line 49
($context["b5_navbar_bg_schema"] ?? null) != "none")) ? ((" bg-" . $this->sandbox->ensureToStringAllowed(($context["b5_navbar_bg_schema"] ?? null), 49, $this->source))) : (" ")));
        // line 51
        echo "
";
        // line 53
        $context["footer_classes"] = (((" " . (((        // line 54
($context["b5_footer_schema"] ?? null) != "none")) ? ((" footer-" . $this->sandbox->ensureToStringAllowed(($context["b5_footer_schema"] ?? null), 54, $this->source))) : (" "))) . (((        // line 55
($context["b5_footer_schema"] ?? null) != "none")) ? ((((($context["b5_footer_schema"] ?? null) == "dark")) ? (" text-light") : (" text-dark"))) : (" "))) . (((        // line 56
($context["b5_footer_bg_schema"] ?? null) != "none")) ? ((" bg-" . $this->sandbox->ensureToStringAllowed(($context["b5_footer_bg_schema"] ?? null), 56, $this->source))) : (" ")));
        // line 58
        echo "
<header class=\"main-header\">
  <div class=\"main-header__top\">
    <div class=\"container\">
      <a target=\"blank\" href=\"https://www.gov.co/\">
        <img src=\"";
        // line 63
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["base_path"] ?? null), 63, $this->source), "html", null, true);
        echo "/img/govco.png\" alt=\"Imagen logo GovCo\">
        <span class=\"sr-only\">Logo Gobierno de Colombia</span>
      </a>
    </div>
  </div>

  ";
        // line 69
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "header", [], "any", false, false, true, 69), 69, $this->source), "html", null, true);
        echo "
  <div class=\"main-header__bottom\">
    <div class=\"container\">
      ";
        // line 72
        if (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_branding", [], "any", false, false, true, 72) || twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_main", [], "any", false, false, true, 72)) || twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_additional", [], "any", false, false, true, 72))) {
            // line 73
            echo "      <nav class=\"";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["nav_classes"] ?? null), 73, $this->source), "html", null, true);
            echo " main-menu\">
        <div class=\"";
            // line 74
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["b5_top_container"] ?? null), 74, $this->source), "html", null, true);
            echo " d-flex main-menu__wrapper\">
          ";
            // line 75
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_branding", [], "any", false, false, true, 75), 75, $this->source), "html", null, true);
            echo "

          <button class=\"navbar-toggler collapsed main-menu__button\" type=\"button\" data-bs-toggle=\"collapse\"
                  data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\"
                  aria-expanded=\"false\" aria-label=\"Toggle navigation\">
            <span class=\"navbar-toggler-icon\"></span>
          </button>

          <div class=\"collapse navbar-collapse justify-content-md-end flex-wrap\" id=\"navbarSupportedContent\">
            ";
            // line 84
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_main", [], "any", false, false, true, 84), 84, $this->source), "html", null, true);
            echo "
            ";
            // line 85
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "nav_additional", [], "any", false, false, true, 85), 85, $this->source), "html", null, true);
            echo "
          </div>
        </div>
      </nav>
      ";
        }
        // line 90
        echo "    </div>
  </div>
</header>
";
        // line 93
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "banner_site", [], "any", false, false, true, 93)) {
            // line 94
            echo "<section class=\"hero-banner\">
  <div class=\"container\">
    ";
            // line 96
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "banner_site", [], "any", false, false, true, 96), 96, $this->source), "html", null, true);
            echo "
  </div>
</section>
";
        }
        // line 100
        echo "<main role=\"main\">
  <a id=\"main-content\" tabindex=\"-1\"></a>";
        // line 102
        echo "
  ";
        // line 104
        $context["sidebar_first_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 104) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 104))) ? ("col-12 col-sm-6 col-lg-3") : ("col-12 col-lg-3"));
        // line 106
        echo "
  ";
        // line 108
        $context["sidebar_second_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 108) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 108))) ? ("col-12 col-sm-6 col-lg-3") : ("col-12 col-lg-3"));
        // line 110
        echo "
  ";
        // line 112
        $context["content_classes"] = (((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 112) && twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 112))) ? ("col-12 col-lg-6") : ((((twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 112) || twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 112))) ? ("col-12 col-lg-9") : ("col-12"))));
        // line 114
        echo "

  <div class=\"";
        // line 116
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["b5_top_container"] ?? null), 116, $this->source), "html", null, true);
        echo "\">
    ";
        // line 117
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 117)) {
            // line 118
            echo "      ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 118), 118, $this->source), "html", null, true);
            echo "
    ";
        }
        // line 120
        echo "    <div class=\"row g-0\">
      ";
        // line 121
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 121)) {
            // line 122
            echo "        <div class=\"order-2 order-lg-1 ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["sidebar_first_classes"] ?? null), 122, $this->source), "html", null, true);
            echo "\">
          ";
            // line 123
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_first", [], "any", false, false, true, 123), 123, $this->source), "html", null, true);
            echo "
        </div>
      ";
        }
        // line 126
        echo "      <div class=\"order-1 order-lg-2 ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["content_classes"] ?? null), 126, $this->source), "html", null, true);
        echo "\">
        ";
        // line 127
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 127), 127, $this->source), "html", null, true);
        echo "
      </div>
      ";
        // line 129
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 129)) {
            // line 130
            echo "        <div class=\"order-3 ";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["sidebar_second_classes"] ?? null), 130, $this->source), "html", null, true);
            echo "\">
          ";
            // line 131
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "sidebar_second", [], "any", false, false, true, 131), 131, $this->source), "html", null, true);
            echo "
        </div>
      ";
        }
        // line 134
        echo "    </div>
  </div>
</main>
";
        // line 137
        if (twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 137)) {
            // line 138
            echo "<footer class=\"mt-auto main-footer\">
  <div class=\"container main-footer__wrapper\">
    ";
            // line 140
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 140), 140, $this->source), "html", null, true);
            echo "
  </div>
  <div class=\"main-footer__bottom\">
    <div class=\"container\">
      <div class=\"d-flex justify-content-between align-items-center flex-column flex-md-row\">
      <ul class=\"d-flex m-0 py-3 list-unstyled\">
        <li>
          <img src=\"";
            // line 147
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["base_path"] ?? null), 147, $this->source), "html", null, true);
            echo "/img/marca_co.png\" alt=\"Marca Colombia\" width=\"60\">
          <span class=\"sr-only\">Logo marca Colombia</span>
        </li>
        <li class=\"border-left border-white pl-4 ml-4\"></li>
        <li class=\"align-self-center\">
          <img src=\"";
            // line 152
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["base_path"] ?? null), 152, $this->source), "html", null, true);
            echo "/img/govco.png\" alt=\"Logo Gobierno de Colombia\" width=\"130\">
          <span class=\"sr-only\">Logo Gobierno de Colombia</span>
        </li>
      </ul>
      <a href=\"https://www.gov.co\" target=\"_blank\" class=\"text-white\">Conoce GOV.CO aquí</a>
      </div>
    </div>
  </div>
</footer>
";
        }
    }

    public function getTemplateName()
    {
        return "themes/custom/jutheme/templates/layout/page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  229 => 152,  221 => 147,  211 => 140,  207 => 138,  205 => 137,  200 => 134,  194 => 131,  189 => 130,  187 => 129,  182 => 127,  177 => 126,  171 => 123,  166 => 122,  164 => 121,  161 => 120,  155 => 118,  153 => 117,  149 => 116,  145 => 114,  143 => 112,  140 => 110,  138 => 108,  135 => 106,  133 => 104,  130 => 102,  127 => 100,  120 => 96,  116 => 94,  114 => 93,  109 => 90,  101 => 85,  97 => 84,  85 => 75,  81 => 74,  76 => 73,  74 => 72,  68 => 69,  59 => 63,  52 => 58,  50 => 56,  49 => 55,  48 => 54,  47 => 53,  44 => 51,  42 => 49,  41 => 48,  40 => 47,  39 => 46,);
    }

    public function getSourceContext()
    {
        return new Source("", "themes/custom/jutheme/templates/layout/page.html.twig", "/home/instrumentoju/public_html/web/themes/custom/jutheme/templates/layout/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 46, "if" => 72);
        static $filters = array("escape" => 63);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
