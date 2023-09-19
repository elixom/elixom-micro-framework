This is the PHP7 version of my lib.
Fully Name Spaced

BREAKING CHANGES
- Does not set parent node on child
- No HtmlUtils::loadApi()
- the CONSTRUCTOR(str) is no longer CONSTRUCTOR(name) but rather CONSTRUCTOR(tagName) 
- INPUT::HTML_PATTERN_NAME move to HtmlUtils::getPattern()->HTML_PATTERN_NAME
- checked() as an alias for selected() on OPTION elements

***DEPRECATED***
- reset() empties nodes and empties attributes including name
- empty() only empties nodes
- delete() replaced with remove()
- SELECT->options()  use SELECT->nodes()
- SELECT->item()  use SELECT->nodes()
- FORM->fieldset($legend='') use FORM->addFieldset($legend='')
- TR->addTd()
- TR->addTh()
- TR->cells()
- (TBODY|THEAD|TFOOT)->rows()
- (TBODY|THEAD|TFOOT)->addTr()
- (TBODY|THEAD|TFOOT)->addRow()
- (TBODY|THEAD|TFOOT)->tr()
- (TBODY|THEAD|TFOOT)->table()
- COLGROUP->table()
- COLGROUP->addCol()
- TABLE->()
- HTML_OPT_LEGACYSELECT for datalist
- INPUT->selected() as alias for INPUT->checked()


***REMOVED***
- buildOption()
- INPUT->setType($type) is removed
- LABEL->attach() and LABEL->attached() are removed
- INPUT[type=password]->showToggle() removed
- HTML_input::build removed
- TD->tr()
- TH->tr()
- INPUT->setName()
- INPUT->setType()
- INPUT->options()



***ADDED***
- INPUT type = radio-group, type = checkbox-group
    item wrapped in a SPAN
    automatically converts INPUT[checkbox] and INPUT[radio] to INPUT[checkbox-group] and INPUT[radio-group] if ->AddOption() is called
-
OTHER CHANGES
-HtmlUtils::build == alias of HtmlUtils::create
-HtmlUtils::create and HtmlUtils::input
   both accept the second parameter as an array (attributes) or a string (name|id).
