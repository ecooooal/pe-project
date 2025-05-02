import { EditorState } from "@codemirror/state";
import { EditorView, keymap } from "@codemirror/view";
import { defaultKeymap } from "@codemirror/commands";
import { basicSetup } from "codemirror";
import {python} from "@codemirror/lang-python"
import { java } from "@codemirror/lang-java";
import {cpp} from "@codemirror/lang-cpp"
import {markdown} from "@codemirror/lang-markdown"
import { lineNumbers } from "@codemirror/view"
import { history, historyKeymap } from '@codemirror/commands';

const solution_div = document.getElementById("solution-div");
const test_case_div = document.getElementById("test-case-div");
let instruction_div = document.getElementById('instruction-div');
const states = [];
let instruction_previous_state = null;
let current_lang = null;

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

    export const instruction_state = EditorState.create({
        doc: `Type your Instructions here`,
        extensions: [
            markdown(),            
            history(),                      
            keymap.of([
              ...defaultKeymap,
              ...historyKeymap               
            ]),
            EditorView.lineWrapping,

            EditorView.theme({
              "&": {
                backgroundColor: "#ffffff",
                border: "1px solid #e0e0e0",
                fontFamily: "sans-serif",
                fontSize: "14px",
              },
              ".cm-content": {
                padding: "1rem"
              },
              ".cm-scroller": {
                width: "100%",
                height: "100%",   
                overflow: "auto"
              }
            }, { dark: false })
          ],
    });
    let instruction_editor = new EditorView({
        state: instruction_state,
        parent: instruction_div
    });


    function switchLanguageFromEvent(event) {
        const selectedLanguage = event.target.value;
        switchLanguage(selectedLanguage);
    }

    function switchLanguage(lang) {
        if (current_lang && solution_editor?.state) {
            states[current_lang] = solution_editor.state; 
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

        current_lang = lang;
      }

    function getSolutionCode() {
        document.getElementById('code-input').value = solution_editor.state.doc.toString();
    }

    function getTestCaseCode() {
        document.getElementById('test-input').value = test_case_editor.state.doc.toString();
    }

    function getInstructionCode() {
        document.getElementById('instruction-input').value = instruction_editor.state.doc.toString();
    }
    function previewInstructionCode() {    
        if (instruction_editor.state) {
            instruction_previous_state = instruction_editor.state;
        } else {
            instruction_previous_state = EditorState.create({
                doc: instruction_editor.state.doc.toString(),
                extensions: [
                    markdown(),            
                    history(),                       
                    keymap.of([ 
                        ...defaultKeymap,
                        ...historyKeymap               
                    ]),
                    EditorView.lineWrapping,
                    EditorView.theme({
                        "&": {
                            backgroundColor: "#ffffff",
                            border: "1px solid #e0e0e0",
                            fontFamily: "sans-serif",
                            fontSize: "14px",
                        },
                        ".cm-content": {
                            padding: "1rem"
                        },
                        ".cm-scroller": {
                            width: "100%",
                            height: "100%",   
                            overflow: "auto"
                        }
                    }, { dark: false })
                ],  
            });
        }
        const input = document.getElementById('instruction-preview-input');
        if (input) {
            input.value = instruction_editor.state.doc.toString();
        } else {
            console.warn("Element #instruction-preview-input not found.");
        }
        }
    
    function getPreviousInstructionCode(){
        console.log('Previous state:', instruction_previous_state);
        renderEditor(instruction_previous_state);

    }

    function renderEditor(state) {
        instruction_editor = new EditorView({
            state: state,
            parent: instruction_div
        });
    }
    document.body.addEventListener('htmx:afterSwap', () => {
        instruction_div = document.getElementById('instruction-div');
    });
    document.body.addEventListener('htmx:afterSwap', function (evt) {
        if (evt.target.id === 'instruction-div') {
          getPreviousInstructionCode();
        }
    });

window.switchLanguageFromEvent = switchLanguageFromEvent;
window.switchLanguage = switchLanguage;
window.getSolutionCode = getSolutionCode;
window.getTestCaseCode = getTestCaseCode;
window.getInstructionCode = getInstructionCode;
window.previewInstructionCode = previewInstructionCode;
window.getPreviousInstructionCode = getPreviousInstructionCode;



