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

/* themes/custom/jutheme/templates/webform/webform-progress-tracker.html.twig */
class __TwigTemplate_2f572ac409d223d6400c9db729df431594701d0910dd998ac59ee6f4089fa46b extends \Twig\Template
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
        // line 20
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("webform/webform.progress.tracker"), "html", null, true);
        echo "
<div class=\"js_gn_carousel_controls progress__pagination__prev\">
  <span class=\"js_gn_carousel__prev_arrow\">
    <i class=\"fa-solid fa-angle-left progress__pagination__icon\"></i>
  </span>
</div>
<div class=\"js_gn_carousel_controls progress__pagination__next\">
  <span class=\"js_gn_carousel__next_arrow\">
    <i class=\"fa-solid fa-angle-right progress__pagination__icon\"></i>
  </span>
</div>
<ul class=\"webform-progress-tracker progress-tracker progress-tracker--center progress\" data-webform-progress-steps>
  ";
        // line 32
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["progress"] ?? null));
        foreach ($context['_seq'] as $context["index"] => $context["page"]) {
            // line 33
            echo "    ";
            $context["is_completed"] = ($context["index"] < ($context["current_index"] ?? null));
            // line 34
            echo "    ";
            $context["is_active"] = ($context["index"] == ($context["current_index"] ?? null));
            // line 35
            echo "    ";
            // line 36
            $context["classes"] = [0 => "progress-step", 1 => "progress__step", 2 => ((            // line 39
($context["is_completed"] ?? null)) ? ("is-complete progress__step--complete") : ("")), 3 => ((            // line 40
($context["is_active"] ?? null)) ? ("is-active progress__step--active") : (""))];
            // line 43
            echo "    ";
            // line 44
            $context["attributes"] = twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(), "setAttribute", [0 => ("data-webform-" . $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source,             // line 45
$context["page"], "type", [], "any", false, false, true, 45), 45, $this->source)), 1 => twig_get_attribute($this->env, $this->source, $context["page"], "name", [], "any", false, false, true, 45)], "method", false, false, true, 44), "setAttribute", [0 => "title", 1 => twig_get_attribute($this->env, $this->source,             // line 46
$context["page"], "title", [], "any", false, false, true, 46)], "method", false, false, true, 45), "setAttribute", [0 => "class", 1 => ""], "method", false, false, true, 46), "addClass", [0 =>             // line 48
($context["classes"] ?? null)], "method", false, false, true, 47);
            // line 50
            echo "    <li";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["attributes"] ?? null), 50, $this->source), "html", null, true);
            echo ">
      <div class=\"progress__wrapper\">
        <div class=\"progress__caption\">
          ";
            // line 53
            if ((twig_length_filter($this->env, ($context["progress"] ?? null)) < ($context["max_pages"] ?? null))) {
                // line 54
                echo "            <div class=\"progress-text progress__text\">
              <div class=\"progress-title progress__title\" data-webform-progress-link>
                <span class=\"progress-marker progress__marker\" data-webform-progress-step data-webform-progress-link data-text=\"";
                // line 56
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["index"] + 1), "html", null, true);
                echo "\"></span>
                <span class=\"visually-hidden\" data-webform-progress-state>";
                // line 57
                if ((($context["is_active"] ?? null) || ($context["is_completed"] ?? null))) {
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(((($context["is_active"] ?? null)) ? (t("Current")) : (t("Completed"))));
                }
                echo "</span>
                ";
                // line 58
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["page"], "title", [], "any", false, false, true, 58), 58, $this->source), "html", null, true);
                echo "
              </div>
            </div>
          ";
            }
            // line 62
            echo "        </div>
      </div>
    </li>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['index'], $context['page'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 66
        echo "</ul>
";
    }

    public function getTemplateName()
    {
        return "themes/custom/jutheme/templates/webform/webform-progress-tracker.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  116 => 66,  107 => 62,  100 => 58,  94 => 57,  90 => 56,  86 => 54,  84 => 53,  77 => 50,  75 => 48,  74 => 46,  73 => 45,  72 => 44,  70 => 43,  68 => 40,  67 => 39,  66 => 36,  64 => 35,  61 => 34,  58 => 33,  54 => 32,  39 => 20,);
    }

    public function getSourceContext()
    {
        return new Source("", "themes/custom/jutheme/templates/webform/webform-progress-tracker.html.twig", "/home/instrumentoju/public_html/web/themes/custom/jutheme/templates/webform/webform-progress-tracker.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("for" => 32, "set" => 33, "if" => 53);
        static $filters = array("escape" => 20, "length" => 53, "t" => 57);
        static $functions = array("attach_library" => 20, "create_attribute" => 44);

        try {
            $this->sandbox->checkSecurity(
                ['for', 'set', 'if'],
                ['escape', 'length', 't'],
                ['attach_library', 'create_attribute']
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
