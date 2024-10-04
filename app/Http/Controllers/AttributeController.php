<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function create_xmi($request){
        $xmiContent = '<?xml version="1.0" encoding="UTF-8"?>
        <XMI>
          <Model name="ExampleModel">
            <Class name="ExampleClass">
              <Attribute name="id" type="Integer"/>
              <Attribute name="name" type="String"/>
            </Class>
            <Class name="AnotherClass">
              <Attribute name="description" type="Text"/>
              <Association from="ExampleClass" to="AnotherClass"/>
            </Class>
          </Model>
        </XMI>';
        return $xmiContent;
    }
}
