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
import { placeholder } from "@codemirror/view";

    // Initialize Constant Variables
    const solution_div = document.getElementById("solution-div");
    const initial_solution_div = document.getElementById("initial-solution-div");
    const test_case_div = document.getElementById("test-case-div");
    const select_form = document.getElementById('programming_language');
    const available_languages = ['c++', 'java', 'python'];
    const supported_languages = {};
    let instruction_div = document.getElementById('instruction-div');
    let instruction_previous_state = null;
    let previous_language = null;
    let currentLanguage = null;
    

    const solution_editor = createEditor(solution_div);
    const initial_solution_editor = createEditor(initial_solution_div);
    const test_case_editor = createEditor(test_case_div);


    // Initialize States for Code Editors
    const instruction_state = EditorState.create({
        doc:`## Instruction for Creating Coding Question 

1. ✅ **Problem Summary** — A one- or two-sentence overview of what the user is supposed to do.
2. ✅ **Detailed Instructions** — Explain what the function should do, how it should behave, and any important constraints or edge cases.
3. ✅ **Examples** — Provide input/output examples using code blocks to make it easy to read and copy.
4. ✅ **Language-Specific Details** — If the function name or behavior is specific in one language, explain it clearly.
5. ✅ **Formatting with Markdown** — Use headers, lists, bold, italics, and code formatting to make the instruction visually clear.

---

#### Title: Convert String to Lowercase

### Description

Write a function that takes a string as input and returns the same string in all lowercase letters.

### Function Signature (Java)

\`\`\`java
public static String exampleJavaMethod(String input)
\`\`\`

### Examples

\`\`\`java
exampleJavaMethod("Hello World") ➞ "hello world"
exampleJavaMethod("CODEWARS") ➞ "codewars"
exampleJavaMethod("123ABC!") ➞ "123abc!"
\`\`\`

### Notes

* The input string may contain letters, numbers, and special characters.
* Only alphabetic characters will be changed to lowercase.
* You may use built-in string methods.
---
`,
        extensions: [
            placeholder("Type your Instructions here"),
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
                fontFamily: "sans-serif",
                fontSize: "14px",
              },
              ".cm-content": {
                maxHeight: "28rem",
                padding: "1rem",
              },
              ".cm-scroller": {
                minHeight: "16rem",
                maxHeight:"28rem",
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

    function createEditor(parent) {
        const state = EditorState.create({ extensions: [basicSetup] });
        return new EditorView({ state, parent });
      }

    const onChangeListener = EditorView.updateListener.of((update) => {
        if (update.docChanged && currentLanguage) {

            if (supported_languages[currentLanguage]) {
                supported_languages[currentLanguage].is_valid = false;
                
                supported_languages[currentLanguage].complete_solution = solution_editor.state;
                supported_languages[currentLanguage].initial_solution = initial_solution_editor.state;
                supported_languages[currentLanguage].test_case = test_case_editor.state;
                updateLanguageBadges();
                console.log(`[${currentLanguage}] marked as invalid and states updated`);
            } else {
                console.warn(`Language not found: ${currentLanguage}`);
            }
        }
    });


    // Add ability to switch programming languages and have their own codes
    function switchLanguageFromEvent(event) {
        const selected_language = event.target.value;
      
        select_form.addEventListener('pointerdown', () => {
          previous_language = select_form.value;
        });
        console.log(`${previous_language} is included in available languages? ${available_languages.includes(previous_language)}`);

        if (previous_language && previous_language != selected_language && available_languages.includes(previous_language)) {
            console.log('got run')
            updateSupportedLanguages({
                language: previous_language,
                complete_solution: solution_editor.state,
                initial_solution: initial_solution_editor.state,
                test_case: test_case_editor.state,
            });
        }
        switchLanguage(selected_language);
    }

    // Reinitialize code editors to switch languages
    function switchLanguage(language) {
        currentLanguage = language;
        if (supported_languages.hasOwnProperty(language) && supported_languages[language] != null) {
            solution_editor.setState(supported_languages[language]['complete_solution']);
            initial_solution_editor.setState(supported_languages[language]['initial_solution']);
            test_case_editor.setState(supported_languages[language]['test_case']);

        } else {
                let languageExtension;
                let placeholder_solution = placeholder(`Insert complete solution here`);
                let placeholder_initial = placeholder(`This Code will be given to students.`);
                let placeholder_test = placeholder(`Insert test case here`);
                let test_case_scaffold = "";
                let complete_solution_scaffold = "";
                let initial_solution_scaffold = "";
                switch (language) {
                    case 'java':
                        languageExtension = java();
                        complete_solution_scaffold = `// Below are working example of a java code complete solution
public class ExampleJavaCompleteSolution {
// This method takes a string, converts it to lowercase, and returns it
    public static String exampleJavaMethod(String input) {
        return input.toLowerCase();
    }
}
`;
                        initial_solution_scaffold = `public class ExampleJavaCompleteSolution {
    public static String exampleJavaMethod(String input) {
        // Initial Solution should only return pass
        return pass;
    }
}
`;
                        test_case_scaffold = `import org.junit.jupiter.api.Test;
import static org.junit.jupiter.api.Assertions.*;

// Java uses JUnit5 for testing
// Below are working example for java test cases
public class ExampleJavaCompleteSolutionTest {

    @Test
    void upperCaseShouldBeLowercased() {
        String result = ExampleJavaCompleteSolution.exampleJavaMethod("HELLO WORLD");
        assertEquals("hello world", result); // This should pass
    }

    @Test
    void mixedCaseShouldBeLowercased() {
        String result = ExampleJavaCompleteSolution.exampleJavaMethod("HeLLo WoRLd");
        assertEquals("hello world", result); // This should also pass
    }

    @Test
    void alreadyLowercaseShouldRemainSame() {
        String result = ExampleJavaCompleteSolution.exampleJavaMethod("hello world");
        assertEquals("hello world", result); // This ensures no change if already lowercase
    }
}
`;
                        break;
                    case 'python':
                        languageExtension = python();
                        break;
                    case 'c++':
                        languageExtension = cpp();
                        break;
                    default:
                        console.warn("Unsupported language:", language);
                        return;
                }

            const new_solution_state = EditorState.create({
                doc: complete_solution_scaffold,
                extensions: [basicSetup, languageExtension, placeholder_solution, onChangeListener],
            });
            const new_initial_solution_state = EditorState.create({
                doc: initial_solution_scaffold,
                extensions: [basicSetup, languageExtension, placeholder_initial, onChangeListener],
            });
            const new_test_case_state = EditorState.create({
                doc: test_case_scaffold,
                extensions: [basicSetup, languageExtension, placeholder_test, onChangeListener],
            });
            
            solution_editor.setState(new_solution_state);
            initial_solution_editor.setState(new_initial_solution_state);
            test_case_editor.setState(new_test_case_state);

        }

        updateSupportedLanguages({
            language,
            complete_solution: solution_editor.state,
            initial_solution: initial_solution_editor.state,
            test_case: test_case_editor.state,
        });
    }
    
    // Create language object and add to an array to keep track
    function updateSupportedLanguages({ language, ...updates }) {
        if (!language) {
            throw new Error("The 'language' field is required.");
        }
    
        if (!supported_languages[language]) {
            supported_languages[language] = {
                complete_solution: '',
                initial_solution: '',
                test_case: '',
                is_valid: false
            };
        }
    
        Object.assign(supported_languages[language], updates);
    }
    
    function getSolutionCode() {
        const processed = {};

        for (const lang in supported_languages) {
            const entry = supported_languages[lang];
            if (entry.is_valid){
                processed[lang] = {
                    complete_solution: entry.complete_solution.doc.toString(),
                    initial_solution: entry.initial_solution.doc.toString(),
                    test_case: entry.test_case.doc.toString(),
                    is_valid: entry.is_valid,
                }
            };
        }

        document.getElementById('code-input').value = JSON.stringify(processed);
    }
    

    function getTestCaseCode() {
        document.getElementById('test-input').value = test_case_editor.state.doc.toString();
    }

    function validateLanguageCompleteSolution(){
        console.log("Preparing form...");
        const syntax = document.querySelector('#points-forms input[name="syntax_points"]')?.value;
        const runtime = document.querySelector('#points-forms input[name="runtime_points"]')?.value;
        const testCase = document.querySelector('#points-forms input[name="test_case_points"]')?.value;
        const syntax_deduction = document.querySelector('#points-deduction-forms input[name="syntax_points_deduction"]')?.value;
        const runtime_deduction = document.querySelector('#points-deduction-forms input[name="runtime_points_deduction"]')?.value;
        const testCase_deduction = document.querySelector('#points-deduction-forms input[name="test_case_points_deduction"]')?.value;
         
        document.getElementById('validate_syntax_points_hidden').value = syntax;
        document.getElementById('validate_runtime_points_hidden').value = runtime;
        document.getElementById('validate_test_case_points_hidden').value = testCase;
        document.getElementById('validate_syntax_points_deduction_hidden').value = syntax_deduction;
        document.getElementById('validate_runtime_points_deduction_hidden').value = runtime_deduction;
        document.getElementById('validate_test_case_points_deduction_hidden').value = testCase_deduction;
        
        document.getElementById('validate-complete-solution-input').value = solution_editor.state.doc.toString();
        document.getElementById('validate-test-case-input').value = test_case_editor.state.doc.toString();
        document.getElementById('language_to_validate-input').value = select_form.value;
        return true;
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
                            minHeight: "16rem",
                            padding: "1rem"
                          },
                          ".cm-scroller": {
                            minHeight: "16rem",
                            maxHeight:"24rem",
                            width: "100%",
                            height: "100%",   
                            overflow: "auto"
                          }
                    }, { dark: false })
                ],  
            });

        }
        let input = document.getElementById('instruction-preview-input');

        if (input) {
            input.value = instruction_editor.state.doc.toString();
        } else {
            console.warn("Element #instruction-preview-input not found.");
        }
    }
    
    function getPreviousInstructionCode(){
        setTimeout(() => {
            instruction_editor = new EditorView({
                state: instruction_previous_state,
                parent: instruction_div
            });
        }, 500); 
    }
    function attachButtonsListener() {
        const preview_button = document.getElementById("preview-button");
        const edit_button = document.getElementById("edit-button");        
        if (preview_button) {
            preview_button.addEventListener("click", previewInstructionCode);
        }
        if (edit_button) {
            edit_button.addEventListener("click", getPreviousInstructionCode);
        }
    }

    document.body.addEventListener("htmx:afterSwap", function(evt) {
        instruction_div = document.getElementById("instruction-div");      

        if (['preview-button', 'edit-button'].includes(evt.detail.target.id)) {
            attachButtonsListener();
        }
        
    });
    document.addEventListener("DOMContentLoaded", () => {
        const programming_language_selected = document.getElementById('programming_language');
        attachButtonsListener();
        if (programming_language_selected) {
            switchLanguageFromEvent({ target: { value: programming_language_selected.value } });
        } 
    });
    function temporarilyDisable(button) {
        setTimeout(() => {
            button.disabled = true;
            setTimeout(() => {
              button.disabled = false;
            }, 1000); // 1 second cooldown
          }, 0);
      }

    document.addEventListener('htmx:beforeRequest', function () {
        document.querySelectorAll('#button-actions button').forEach(btn => btn.disabled = true);
    });

    document.body.addEventListener('htmx:afterRequest', function () {
        document.querySelectorAll('#button-actions button').forEach(btn => btn.disabled = false);
        const input = document.getElementById('language-validation-status');
        if (input) {
            const language = input.dataset.language;
            const isValid = input.dataset.valid === 'true';

            if (supported_languages[language]) {
                supported_languages[language].is_valid = isValid;
                console.log(`✅ ${language} marked as valid:`, isValid);
                updateLanguageBadges();
            }

            input.remove(); 
        }
    });

    function updateLanguageBadges() {
        const select = document.getElementById('programming_language');
        for (const option of select.options) {
            const lang = option.value;
            if (supported_languages[lang]?.is_valid) {
                if (!option.text.includes('✅')) {
                    option.text = `${lang} ✅`;
                }
            } else {
                option.text = lang;
            }
        }
    }




window.switchLanguageFromEvent = switchLanguageFromEvent;
window.switchLanguage = switchLanguage;
window.getSolutionCode = getSolutionCode;
window.getTestCaseCode = getTestCaseCode;
window.getInstructionCode = getInstructionCode;
window.validateLanguageCompleteSolution = validateLanguageCompleteSolution;
window.temporarilyDisable = temporarilyDisable;




