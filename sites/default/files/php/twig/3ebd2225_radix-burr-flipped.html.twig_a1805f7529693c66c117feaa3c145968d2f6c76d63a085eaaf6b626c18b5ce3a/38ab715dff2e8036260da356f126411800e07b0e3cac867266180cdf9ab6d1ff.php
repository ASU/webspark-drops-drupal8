<?php

/* profiles/panopoly/modules/contrib/radix_layouts/plugins/layouts/radix_burr_flipped/radix-burr-flipped.html.twig */
class __TwigTemplate_cd94f304bda6a0a8c75e39c8f5f55dd7d447011c30f2173ff46034f0f8b2c82c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("if" => 13);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array('if'),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setTemplateFile($this->getTemplateName());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 12
        echo "
<div class=\"panel-display burr-flipped clearfix ";
        // line 13
        if ((isset($context["classes"]) ? $context["classes"] : null)) {
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["classes"]) ? $context["classes"] : null), "html", null, true));
        }
        if ((isset($context["class"]) ? $context["class"] : null)) {
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["class"]) ? $context["class"] : null), "html", null, true));
        }
        echo "\" ";
        if ((isset($context["css_id"]) ? $context["css_id"] : null)) {
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["css_id"]) ? $context["css_id"] : null), "html", null, true));
        }
        echo ">
  
  <div class=\"container-fluid\">
    <div class=\"row\">
      <div class=\"col-md-8 radix-layouts-content panel-panel\">
        <div class=\"panel-panel-inner\">
          ";
        // line 19
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["content"]) ? $context["content"] : null), "contentmain", array()), "html", null, true));
        echo "
        </div>
      </div>
      <div class=\"col-md-4 radix-layouts-sidebar panel-panel\">
        <div class=\"panel-panel-inner\">
          ";
        // line 24
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $this->getAttribute((isset($context["content"]) ? $context["content"] : null), "sidebar", array()), "html", null, true));
        echo "
        </div>
      </div>
    </div>
  
  </div>
</div><!-- /.burr-flipped -->
";
    }

    public function getTemplateName()
    {
        return "profiles/panopoly/modules/contrib/radix_layouts/plugins/layouts/radix_burr_flipped/radix-burr-flipped.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 24,  64 => 19,  46 => 13,  43 => 12,);
    }
}
/* {#*/
/* /***/
/*  * @file*/
/*  * Template for Radix Burr Flipped.*/
/*  **/
/*  * Variables:*/
/*  * - $css_id: An optional CSS id to use for the layout.*/
/*  * - $content: An array of content, each item in the array is keyed to one*/
/*  * panel of the layout. This layout supports the following sections:*/
/*  *//* */
/* #}*/
/* */
/* <div class="panel-display burr-flipped clearfix {% if classes %}{{ classes }}{% endif %}{% if class %}{{ class }}{% endif %}" {% if css_id %}{{ css_id }}{% endif %}>*/
/*   */
/*   <div class="container-fluid">*/
/*     <div class="row">*/
/*       <div class="col-md-8 radix-layouts-content panel-panel">*/
/*         <div class="panel-panel-inner">*/
/*           {{ content.contentmain }}*/
/*         </div>*/
/*       </div>*/
/*       <div class="col-md-4 radix-layouts-sidebar panel-panel">*/
/*         <div class="panel-panel-inner">*/
/*           {{ content.sidebar }}*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/*   */
/*   </div>*/
/* </div><!-- /.burr-flipped -->*/
/* */
