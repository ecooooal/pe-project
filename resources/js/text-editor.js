import { EditorState } from "@codemirror/state";
import { EditorView, keymap } from "@codemirror/view";
import { defaultKeymap } from "@codemirror/commands";
import { basicSetup } from "codemirror";
import {python} from "@codemirror/lang-python"
import { java } from "@codemirror/lang-java";
import {cpp} from "@codemirror/lang-cpp"

const solution_div = document.getElementById("solution-div");
const test_case_div = document.getElementById("test-case-div");
const available_languages = ['python', 'java'];
const states = [];
let currentLang = null;

    export const solution_state = EditorState.create({
        extensions: [basicSetup],
    });
    export const solution_editor = new EditorView({
        state: solution_state,
        parent: solution_div
    });

    export const test_case_state = EditorState.create({
        doc: `Class ClassName {\n \tpublic static data_type function_name() {\n\t //Code Here \n}`,
        extensions: [basicSetup],
    });
    export const test_case_editor = new EditorView({
        state: test_case_state,
        parent: test_case_div
    });


    function switchLanguageFromEvent(event) {
        const selectedLanguage = event.target.value;
        switchLanguage(selectedLanguage);
    }

    function switchLanguage(lang) {
        if (currentLang && solution_editor?.state) {
            states[currentLang] = solution_editor.state; 
        }

        let languageExtension;
        let doc;

        switch (lang) {
            case 'java':
                languageExtension = java();
                doc = `Class ClassName {\n public static data_type function_name() {\n   //Code Here \n  }\n}`;
                break;
            case 'python':
                languageExtension = python();
                doc = `def test_function():\n    pass`;
                break;
            case 'c++':
                languageExtension = cpp();
                doc = `#include <string>\n#include <vector>\n\nReturnType functionName(const std::vector<std::string> &names) {\n  // Code here\n}`;
                break;
            default:
                console.warn("Unsupported language:", lang);
                return;
        }

        if (states[lang]) {
            solution_editor.setState(states[lang]);
        } else {
            const newState = EditorState.create({
                doc: doc,
                extensions: [basicSetup, languageExtension],
            });
            solution_editor.setState(newState);
        }

        currentLang = lang;
      }

    function getSolutionCode() {
        document.getElementById('code-input').value = solution_editor.state.doc.toString();
    }

    function getTestCaseCode() {
        document.getElementById('test-input').value = test_case_editor.state.doc.toString();
    }

window.switchLanguageFromEvent = switchLanguageFromEvent;
window.switchLanguage = switchLanguage;
window.getSolutionCode = getSolutionCode;
window.getTestCaseCode = getTestCaseCode;

